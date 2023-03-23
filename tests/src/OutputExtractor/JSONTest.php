<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\OutputExtractor\JSON as JSON;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;

require_once __DIR__ . "/../../phpunit.php";





class JSONTests extends TestCase
{


    private string $rootPath = "";
    private string $vendorPath = "";
    private string $pathToTestClasses = "";
    private string $pathToTestJSONs = "";
    private string $pathToTestXMLs = "";
    private string $pathToOutputDocs = "";

    private function setTestDirs(): void
    {
        if ($this->rootPath === "") {
            $this->rootPath = realpath(__DIR__ . "/../../../");
            $this->vendorPath = $this->rootPath . "/vendor";
            $this->pathToTestClasses = $this->rootPath . "/tests/resources/testClasses";
            $this->pathToTestJSONs = $this->rootPath . "/tests/resources/testJSONs";
            $this->pathToTestXMLs = $this->rootPath . "/tests/resources/testXMLs";
            $this->pathToOutputDocs = $this->rootPath . "/tests/resources/testOutputDocs";
        }
    }





    public function test_method_fails_extract()
    {
        $this->setTestDirs();

        $proDoc = new ProjectDocumentation($this->vendorPath);
        $obj = new JSON();

        $fail = false;
        try {
            $obj->extract(
                $proDoc,
                "invalid",
                true
            );
        } catch (\Exception $ex) {
            $fail = true;
            $this->assertSame("Directory not found. [ invalid ]", $ex->getMessage());
        }
        $this->assertTrue($fail, "Test must fail");
    }



    public function test_method_extract_toSingleFile()
    {
        $this->setTestDirs();

        $proDoc = new ProjectDocumentation(
            $this->vendorPath,
            [
                $this->rootPath . "/src/Functions"
            ],
            [
                $this->rootPath . "/src/bootstrap.php"
            ]
        );
        $obj = new JSON();

        $expectedDir = $this->pathToOutputDocs . "/JSONSingle/expected";
        $resultDir = $this->pathToOutputDocs . "/JSONSingle/result";

        $this->assertTrue(
            $obj->extract(
                $proDoc,
                $resultDir,
                true
            )
        );

        $expectedFiles = \dir_scan_w_r($expectedDir);
        $resultFiles = \dir_scan_w_r($resultDir);

        $this->assertTrue(\count($resultFiles) > 0);
        $this->assertEquals(\count($expectedFiles), \count($resultFiles));

        foreach ($resultFiles as $resultFilePath) {
            $expectedFilePath = \str_replace($resultDir, $expectedDir, $resultFilePath);
            $this->assertTrue(\is_file($expectedFilePath));

            $expectedContent = \file_get_contents($expectedFilePath);
            $resultContent = \file_get_contents($resultFilePath);

            $this->assertEquals($expectedContent, $resultContent);
        }
    }



    public function test_method_extract_toMultipleFiles()
    {
        $this->setTestDirs();

        $proDoc = new ProjectDocumentation(
            $this->vendorPath,
            [
                $this->rootPath . "/src/Functions"
            ],
            [
                $this->rootPath . "/src/bootstrap.php"
            ]
        );
        $obj = new JSON();

        $expectedDir = $this->pathToOutputDocs . "/JSONMultiple/expected";
        $resultDir = $this->pathToOutputDocs . "/JSONMultiple/result";

        $this->assertTrue(
            $obj->extract(
                $proDoc,
                $resultDir,
                false
            )
        );

        $expectedFiles = \dir_scan_w_r($expectedDir);
        $resultFiles = \dir_scan_w_r($resultDir);

        $this->assertTrue(\count($resultFiles) > 0);
        $this->assertEquals(\count($expectedFiles), \count($resultFiles));

        foreach ($resultFiles as $resultFilePath) {
            $expectedFilePath = \str_replace($resultDir, $expectedDir, $resultFilePath);
            $this->assertTrue(\is_file($expectedFilePath));

            $expectedContent = \file_get_contents($expectedFilePath);
            $resultContent = \file_get_contents($resultFilePath);

            $this->assertEquals($expectedContent, $resultContent);
        }
    }
}