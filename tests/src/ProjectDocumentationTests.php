<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;

require_once __DIR__ . "/../phpunit.php";






class ProjectDocumentationTests extends TestCase
{



    private string $rootPath = "";
    private string $vendorPath = "";
    private string $pathToTestClasses = "";
    private string $pathToTestJSONs = "";
    private string $pathToTestXMLs = "";

    private function setTestDirs(): void
    {
        if ($this->rootPath === "") {
            $this->rootPath = realpath(__DIR__ . "/../../");
            $this->vendorPath = $this->rootPath . "/vendor";
            $this->pathToTestClasses = $this->rootPath . "/tests/resources/testClasses";
            $this->pathToTestJSONs = $this->rootPath . "/tests/resources/testJSONs";
            $this->pathToTestXMLs = $this->rootPath . "/tests/resources/testXMLs";
        }
    }




    public function test_constructor_fails_dirOrfileNotFound()
    {
        $fail = false;
        try {
            $obj = new ProjectDocumentation(
                "invalid",
            );
        } catch (\Exception $ex) {
            $fail = true;
            $this->assertSame("Directory not found. [ invalid ]", $ex->getMessage());
        }
        $this->assertTrue($fail, "Test must fail");


        $fail = false;
        try {
            $obj = new ProjectDocumentation(
                __DIR__,
            );
        } catch (\Exception $ex) {
            $fail = true;
            $this->assertSame("File not found. [ " . __DIR__ . "/composer/autoload_classmap.php ]", $ex->getMessage());
        }
        $this->assertTrue($fail, "Test must fail");
    }



    public function test_constructor_ok()
    {
        $this->setTestDirs();

        $obj = new ProjectDocumentation(
            $this->vendorPath
        );
        $this->assertTrue(is_a($obj, ProjectDocumentation::class));
    }



    public function test_method_get()
    {
        $this->setTestDirs();

        $obj = new ProjectDocumentation(
            $this->vendorPath,
            [
                $this->rootPath . "/src/Functions"
            ],
            [
                $this->rootPath . "/src/bootstrap.php"
            ]
        );



        // FileNames
        $jsonData = \json_encode(
            $obj->getFileNames(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj06.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj06.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Namespaces
        $jsonData = \json_encode(
            $obj->getNamespaces(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj07.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj07.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);



        // Constants
        $jsonData = \json_encode(
            $obj->getConstants(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj08.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj08.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Variables
        $jsonData = \json_encode(
            $obj->getVariables(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj09.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj09.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);



        // Functions
        $jsonData = \json_encode(
            $obj->getFunctions(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj10.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj10.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);



        // Interfaces
        $jsonData = \json_encode(
            $obj->getInterfaces(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj11.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj11.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Enuns
        $jsonData = \json_encode(
            $obj->getEnuns(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj12.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj12.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Traits
        $jsonData = \json_encode(
            $obj->getTraits(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj13.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj13.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Classes
        $jsonData = \json_encode(
            $obj->getClasses(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($this->pathToTestJSONs . "/ExpectedObj14.json");
        \file_put_contents($this->pathToTestJSONs . "/ResultObj14.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);
    }
}