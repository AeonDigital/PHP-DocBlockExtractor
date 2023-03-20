<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\DocBlockExtractor as DocBlockExtractor;

require_once __DIR__ . "/../phpunit.php";






class DocBlockExtractorTests extends TestCase
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



    public function test_static_method_createConfigurationFileTemplate()
    {
        $this->setTestDirs();

        $configFilePath = $this->pathToTestXMLs . "/newConfig.xml";
        if (\is_file($configFilePath) === true) {
            \unlink($configFilePath);
        }

        $this->assertFalse(\is_file($configFilePath));
        $this->assertTrue(DocBlockExtractor::createConfigurationFileTemplate(
            $configFilePath,
            "rootPathTest",
            "vendorPathTest",
            "outputPathTest",
            "JSON",
            false,
            "ONLY/TEST",
            [
                $this->pathToTestXMLs,
                $this->pathToTestXMLs . "/validateConfigXML_01.xml",
                $this->pathToTestXMLs . "/validateConfigXML_02.xml"
            ],
            [
                $this->pathToTestClasses,
                $this->pathToTestJSONs,
                $this->pathToTestXMLs . "/validateConfigXML_03.xml"
            ]
        ));
        $this->assertTrue(\is_file($configFilePath));


        $expectedData = file_get_contents($this->pathToTestXMLs . "/expectedNewConfig.xml");
        $resultData = file_get_contents($configFilePath);
        $this->assertEquals($expectedData, $resultData);
    }



    public function test_static_method_validateConfigXML()
    {
        $this->setTestDirs();
        $exceptionsNamespace = "AeonDigital\\DocBlockExtractor\\Exceptions\\";

        $tests = [
            [
                "xmlFilePath" => "",
                "exceptionType" => "\\InvalidArgumentException",
                "exceptionMessage" => "Path to configuration file cannot be empty."
            ],
            [
                "xmlFilePath" => "nonexist",
                "exceptionType" => $exceptionsNamespace . "FileNotFoundException",
                "exceptionMessage" => "Configuration file not found. [ nonexist ]"
            ],
            [
                "xmlFilePath" => $this->pathToTestXMLs . "/validateConfigXML_01.xml",
                "exceptionType" => $exceptionsNamespace . "InvalidConfigurationFileException",
                "exceptionMessage" => "Cannot parse configuration file. [ " . $this->pathToTestXMLs . "/validateConfigXML_01.xml ]"
            ],
            [
                "xmlFilePath" => $this->pathToTestXMLs . "/validateConfigXML_02.xml",
                "exceptionType" => $exceptionsNamespace . "InvalidConfigurationFileException",
                "exceptionMessage" => "Invalid configuration path. Lost attribute \"vendorDir\"."
            ],
            [
                "xmlFilePath" => $this->pathToTestXMLs . "/validateConfigXML_03.xml",
                "exceptionType" => $exceptionsNamespace . "InvalidConfigurationFileException",
                "exceptionMessage" => "Output extractor class do not implements the required interface \"AeonDigital\DocBlockExtractor\Interfaces\iOutputExtractor\"."
            ],
            [
                "xmlFilePath" => $this->pathToTestXMLs . "/validateConfigXML_04.xml",
                "exceptionType" => $exceptionsNamespace . "DirectoryNotFoundException",
                "exceptionMessage" => "Root path directory does not exists. [ /var/www/html/nonexistent ]"
            ],
            [
                "xmlFilePath" => $this->pathToTestXMLs . "/validateConfigXML_05.xml",
                "exceptionType" => $exceptionsNamespace . "DirectoryNotFoundException",
                "exceptionMessage" => "Vendor directory does not exists. [ /var/www/html/vendorNonExists ]"
            ],
            [
                "xmlFilePath" => $this->pathToTestXMLs . "/validateConfigXML_06.xml",
                "exceptionType" => $exceptionsNamespace . "DirectoryNotFoundException",
                "exceptionMessage" => "Output directory does not exists. [ /var/www/html/docsNonExists ]"
            ],
            [
                "xmlFilePath" => $this->pathToTestXMLs . "/validateConfigXML_07.xml",
                "exceptionType" => $exceptionsNamespace . "InvalidConfigurationFileException",
                "exceptionMessage" => "Invalid output format type. [ JSONN ]"
            ],
            [
                "xmlFilePath" => $this->pathToTestXMLs . "/validateConfigXML_08.xml",
                "exceptionType" => "",
                "exceptionMessage" => ""
            ],
        ];


        foreach ($tests as $test) {
            $xmlFilePath = $test["xmlFilePath"];
            $expectedExceptionType = $test["exceptionType"];
            $expectedExceptionMessage = $test["exceptionMessage"];


            $r = DocBlockExtractor::validateConfigXML($xmlFilePath, true);
            $this->assertIsArray($r);
            $this->assertArrayHasKey("exceptionType", $r);
            $this->assertArrayHasKey("exceptionMessage", $r);
            $this->assertEquals($expectedExceptionType, $r["exceptionType"]);
            $this->assertEquals($expectedExceptionMessage, $r["exceptionMessage"]);
        }


        $this->assertFalse(
            DocBlockExtractor::validateConfigXML($this->pathToTestXMLs . "/validateConfigXML_07.xml")
        );
        $this->assertTrue(
            DocBlockExtractor::validateConfigXML($this->pathToTestXMLs . "/validateConfigXML_08.xml")
        );
    }



    public function test_static_method_retrieveDirectoriesAndFilesFromXMLElement()
    {
        $this->setTestDirs();

        $configXML = \simplexml_load_file($this->pathToTestXMLs . "/retrieveDirectoriesAndFilesFromXMLElement.xml");
        $this->assertTrue(\is_a($configXML, "\\SimpleXMLElement"));
        $this->assertTrue(isset($configXML->detachedFilesAndDirectories));
        $this->assertTrue(isset($configXML->ignoreDetachedFilesAndDirectories));



        $expectedDetached = [
            "/var/www/html/tests/resources/testClasses",
            "/var/www/html/tests/resources/testJSONs",
            "/var/www/html/tests/resources/testXMLs/validateConfigXML_01.xml",
            "/var/www/html/tests/resources/testXMLs/validateConfigXML_02.xml",
            "/var/www/html/tests/resources/testXMLs/validateConfigXML_03.xml",
        ];
        $resultDetached = DocBlockExtractor::retrieveDirectoriesAndFilesFromXMLElement(
            $configXML->detachedFilesAndDirectories,
            "/var/www/html"
        );
        $this->assertEquals(\count($expectedDetached), \count($resultDetached));
        foreach ($expectedDetached as $i => $expected) {
            $this->assertEquals($expected, $resultDetached[$i]);
        }




        $expectedIgnore = [
            "/var/www/html/tests/resources/testXMLs",
            "/var/www/html/tests/resources/testXMLs/validateConfigXML_04.xml",
            "/var/www/html/tests/resources/testXMLs/validateConfigXML_05.xml",
            "/var/www/html/tests/resources/testXMLs/validateConfigXML_06.xml",
        ];
        $resultIgnore = DocBlockExtractor::retrieveDirectoriesAndFilesFromXMLElement(
            $configXML->ignoreDetachedFilesAndDirectories,
            "/var/www/html"
        );
        $this->assertEquals(\count($expectedIgnore), \count($resultIgnore));
        foreach ($expectedIgnore as $i => $expected) {
            $this->assertEquals($expected, $resultIgnore[$i]);
        }
    }



    public function test_static_method_retrieveOutputExtractorInstance()
    {
        $useClassName = "AeonDigital\\DocBlockExtractor\\OutputExtractor\\JSON";
        $resultObj = null;

        $created = false;
        try {
            $resultObj = DocBlockExtractor::retrieveOutputExtractorInstance(
                $useClassName
            );
            $created = true;
        } catch (\Exception $ex) {
            $this->assertSame("Instance not created", $ex->getMessage());
        }
        $this->assertTrue($created);


        $arrImplements = class_implements($resultObj);
        $this->assertIsArray($arrImplements);
        $this->assertTrue(
            \in_array("AeonDigital\\DocBlockExtractor\\Interfaces\\iOutputExtractor", $arrImplements)
        );
    }



    public function test_static_method_main()
    {
        $this->setTestDirs();

        DocBlockExtractor::main(
            $this->pathToTestXMLs . "/mainTestConfig.xml"
        );
    }
}