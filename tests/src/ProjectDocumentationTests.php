<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;
//use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;

require_once __DIR__ . "/../phpunit.php";





class ProjectDocumentationTests extends TestCase
{



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
        $pathToVendor = realpath(__DIR__ . "/../../vendor");
        $obj = new ProjectDocumentation(
            $pathToVendor
        );
        $this->assertTrue(is_a($obj, ProjectDocumentation::class));
    }



    public function test_method_get()
    {
        $pathToExpectedAndResult = realpath(__DIR__ . "/../resources");

        $pathToVendor = realpath(__DIR__ . "/../../vendor");
        $obj = new ProjectDocumentation(
            $pathToVendor
        );



        // FileNames
        $jsonData = \json_encode(
            $obj->getFileNames(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj06.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj06.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Namespaces
        $jsonData = \json_encode(
            $obj->getNamespaces(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj07.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj07.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);



        // Constants
        $jsonData = \json_encode(
            $obj->getConstants(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj08.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj08.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Variables
        $jsonData = \json_encode(
            $obj->getVariables(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj09.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj09.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);



        // Functions
        $jsonData = \json_encode(
            $obj->getFunctions(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj10.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj10.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);



        // Interfaces
        $jsonData = \json_encode(
            $obj->getInterfaces(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj11.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj11.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Enuns
        $jsonData = \json_encode(
            $obj->getEnuns(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj12.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj12.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Traits
        $jsonData = \json_encode(
            $obj->getTraits(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj13.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj13.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);

        // Classes
        $jsonData = \json_encode(
            $obj->getClasses(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $expectedData = file_get_contents($pathToExpectedAndResult . "/ExpectedObj14.json");
        \file_put_contents($pathToExpectedAndResult . "/ResultObj14.json", $jsonData);
        $this->assertEquals($expectedData, $jsonData);
    }
}