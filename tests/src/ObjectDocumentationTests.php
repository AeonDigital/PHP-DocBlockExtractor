<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\ObjectDocumentation as ObjectDocumentation;
use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;

require_once __DIR__ . "/../phpunit.php";





class ObjectDocumentationTests extends TestCase
{

    static ?array $mainExpectedToArray = null;
    static ?array $mainResultToArray = null;



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




    public function test_constructor_fails()
    {
        $this->setTestDirs();

        $fail = false;
        try {
            $obj = new ObjectDocumentation(
                "invalid",
                "",
                ElementType::UNKNOW
            );
        } catch (\Exception $ex) {
            $fail = true;
            $this->assertSame("File not found. [ invalid ]", $ex->getMessage());
        }
        $this->assertTrue($fail, "Test must fail");



        $fail = false;
        try {
            $obj = new ObjectDocumentation(
                $this->pathToTestXMLs . "/retrieveDirectoriesAndFilesFromXMLElement.xml",
                "",
                ElementType::UNKNOW
            );
        } catch (\Exception $ex) {
            $fail = true;
            $this->assertSame(
                "Invalid file extension. Only \".php\" files are allowed [ " . $this->pathToTestXMLs . "/retrieveDirectoriesAndFilesFromXMLElement.xml ]",
                $ex->getMessage()
            );
        }
        $this->assertTrue($fail, "Test must fail");
    }



    public function test_constructor_ok()
    {
        $this->setTestDirs();

        $obj = new ObjectDocumentation(
            $this->pathToTestClasses . "/DocumentationClassTest.php",
            "AeonDigital\\DocBlockExtractor\\Tests\\DocumentationClassTest",
            ElementType::UNKNOW
        );

        $this->assertTrue(is_a($obj, ObjectDocumentation::class));
    }



    public function test_get_properties()
    {
        $this->setTestDirs();
        $fqsen = "AeonDigital\\DocBlockExtractor\\Tests\\DocumentationClassTest";

        $obj = new ObjectDocumentation(
            $this->pathToTestClasses . "/DocumentationClassTest.php",
            $fqsen,
            ElementType::UNKNOW
        );


        $this->assertEquals($this->pathToTestClasses . "/DocumentationClassTest.php", $obj->getFileName());
        $this->assertEquals("AeonDigital\\DocBlockExtractor\\Tests", $obj->getNamespaceName());
        $this->assertEquals($fqsen, $obj->getFQSEN());
        $this->assertEquals("DocumentationClassTest", $obj->getShortName());
        $this->assertEquals(ElementType::CLASSE, $obj->getType());
    }





    public function checkDocBlockArrayMainProperties($expectedObj, $resultObj)
    {
        $this->assertEquals(
            \array_keys($expectedObj),
            \array_keys($resultObj)
        );


        // fileName
        $this->assertEquals(
            $expectedObj["fileName"],
            $resultObj["fileName"],
        );
        // namespaceName
        $this->assertEquals(
            $expectedObj["namespaceName"],
            $resultObj["namespaceName"],
        );
        // fqsen
        $this->assertEquals(
            $expectedObj["fqsen"],
            $resultObj["fqsen"],
        );
        // shortName
        $this->assertEquals(
            $expectedObj["shortName"],
            $resultObj["shortName"],
        );
        // type
        $this->assertEquals(
            $expectedObj["type"],
            $resultObj["type"],
        );
        // docBlock
        $this->assertEquals(
            $expectedObj["docBlock"],
            $resultObj["docBlock"],
        );


        if ($expectedObj["docBlock"] !== [] && \key_exists("tags", $expectedObj["docBlock"]) === true) {
            // docBlock::tags
            $this->assertEquals(
                \array_keys($expectedObj["docBlock"]["tags"]),
                \array_keys($resultObj["docBlock"]["tags"])
            );
            foreach (\array_keys($expectedObj["docBlock"]["tags"]) as $k) {
                $this->assertEquals(
                    $expectedObj["docBlock"]["tags"][$k],
                    $resultObj["docBlock"]["tags"][$k],
                );
            }
        }
    }
    public function checkDocBlockParameterProperties($expectedObj, $resultObj)
    {
        if (\key_exists("parameters", $expectedObj) === true) {
            $this->assertTrue(\key_exists("parameters", $resultObj));

            foreach ($expectedObj["parameters"] as $paramName => $paramData) {
                $this->assertTrue(\key_exists($paramName, $resultObj["parameters"]));

                $this->assertEquals($paramData["type"], $resultObj["parameters"][$paramName]["type"]);
                $this->assertEquals($paramData["isOptional"], $resultObj["parameters"][$paramName]["isOptional"]);
                $this->assertEquals($paramData["isReference"], $resultObj["parameters"][$paramName]["isReference"]);
                $this->assertEquals($paramData["isVariadic"], $resultObj["parameters"][$paramName]["isVariadic"]);
                $this->assertEquals($paramData["isNullable"], $resultObj["parameters"][$paramName]["isNullable"]);
                $this->assertEquals($paramData["isDefaultValue"], $resultObj["parameters"][$paramName]["isDefaultValue"]);
                $this->assertEquals($paramData["defaultValue"], $resultObj["parameters"][$paramName]["defaultValue"]);
                $this->assertEquals($paramData["docBlock"], $resultObj["parameters"][$paramName]["docBlock"]);
            }
        }
    }





    public function test_method_toArray()
    {
        $this->setTestDirs();

        self::$mainExpectedToArray = \json_decode(
            file_get_contents($this->pathToTestJSONs . "/ExpectedObj01_ObjDocumentationTest.json"),
            true
        );


        $obj = new ObjectDocumentation(
            self::$mainExpectedToArray["fileName"],
            self::$mainExpectedToArray["fqsen"],
            ElementType::UNKNOW
        );


        self::$mainResultToArray = $obj->toArray();
        $jsonData = \json_encode(self::$mainResultToArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        \file_put_contents($this->pathToTestJSONs . "/ResultObj01_ObjDocumentationTest.json", $jsonData);


        $this->checkDocBlockArrayMainProperties(
            self::$mainExpectedToArray,
            self::$mainResultToArray
        );


        // docBlock::interfaces
        $this->assertEquals(
            self::$mainExpectedToArray["docBlock"]["interfaces"],
            self::$mainResultToArray["docBlock"]["interfaces"],
        );
        // docBlock::extends
        $this->assertEquals(
            self::$mainExpectedToArray["docBlock"]["extends"],
            self::$mainResultToArray["docBlock"]["extends"],
        );



        // docBlock::isAbstract
        $this->assertEquals(
            self::$mainExpectedToArray["docBlock"]["isAbstract"],
            self::$mainResultToArray["docBlock"]["isAbstract"],
        );
        // docBlock::isFinal
        $this->assertEquals(
            self::$mainExpectedToArray["docBlock"]["isFinal"],
            self::$mainResultToArray["docBlock"]["isFinal"],
        );
    }





    public function test_method_toArray_prop_constants()
    {
        if (self::$mainResultToArray === null) {
            $this->test_method_toArray();
        }

        if (\key_exists("constants", self::$mainExpectedToArray) === true) {
            $this->assertTrue(\key_exists("constants", self::$mainResultToArray));
            $this->assertTrue(\is_array(self::$mainResultToArray["constants"]));
            $this->assertEquals(
                \count(self::$mainExpectedToArray["constants"]["public"]),
                \count(self::$mainResultToArray["constants"]["public"])
            );

            foreach (self::$mainExpectedToArray["constants"]["public"] as $i => $obj) {
                $this->checkDocBlockArrayMainProperties(
                    $obj,
                    self::$mainResultToArray["constants"]["public"][$i]
                );
            }
        }
    }





    public function test_method_toArray_prop_properties()
    {
        if (self::$mainResultToArray === null) {
            $this->test_method_toArray();
        }

        if (\key_exists("properties", self::$mainExpectedToArray) === true) {
            $this->assertTrue(\key_exists("properties", self::$mainResultToArray));
            $this->assertTrue(\is_array(self::$mainResultToArray["properties"]));


            $this->assertEquals(
                \count(self::$mainExpectedToArray["properties"]["public"]["static"]),
                \count(self::$mainResultToArray["properties"]["public"]["static"])
            );
            $this->assertEquals(
                \count(self::$mainExpectedToArray["properties"]["public"]["nonstatic"]),
                \count(self::$mainResultToArray["properties"]["public"]["nonstatic"])
            );


            foreach (self::$mainExpectedToArray["properties"]["public"]["static"] as $i => $obj) {
                $this->checkDocBlockArrayMainProperties(
                    $obj,
                    self::$mainResultToArray["properties"]["public"]["static"][$i]
                );
            }
            foreach (self::$mainExpectedToArray["properties"]["public"]["nonstatic"] as $i => $obj) {
                $this->checkDocBlockArrayMainProperties(
                    $obj,
                    self::$mainResultToArray["properties"]["public"]["nonstatic"][$i]
                );
            }
        }
    }





    public function test_method_toArray_prop_constructor()
    {
        if (self::$mainResultToArray === null) {
            $this->test_method_toArray();
        }

        if (\key_exists("constructor", self::$mainExpectedToArray) === true) {
            $this->assertTrue(\key_exists("constructor", self::$mainResultToArray));
            $this->assertTrue(\is_array(self::$mainResultToArray["constructor"]));

            $this->checkDocBlockArrayMainProperties(
                self::$mainExpectedToArray["constructor"],
                self::$mainResultToArray["constructor"]
            );
        }
    }





    public function test_method_toArray_prop_methods()
    {
        if (self::$mainResultToArray === null) {
            $this->test_method_toArray();
        }

        if (\key_exists("methods", self::$mainExpectedToArray) === true) {
            $this->assertTrue(\key_exists("methods", self::$mainResultToArray));
            $this->assertTrue(\is_array(self::$mainResultToArray["methods"]));



            $this->assertEquals(
                \count(self::$mainExpectedToArray["methods"]["public"]["abstract"]["static"]),
                \count(self::$mainResultToArray["methods"]["public"]["abstract"]["static"])
            );
            $this->assertEquals(
                \count(self::$mainExpectedToArray["methods"]["public"]["abstract"]["nonstatic"]),
                \count(self::$mainResultToArray["methods"]["public"]["abstract"]["nonstatic"])
            );
            $this->assertEquals(
                \count(self::$mainExpectedToArray["methods"]["public"]["nonabstract"]["static"]),
                \count(self::$mainResultToArray["methods"]["public"]["nonabstract"]["static"])
            );
            $this->assertEquals(
                \count(self::$mainExpectedToArray["methods"]["public"]["nonabstract"]["nonstatic"]),
                \count(self::$mainResultToArray["methods"]["public"]["nonabstract"]["nonstatic"])
            );



            foreach (self::$mainExpectedToArray["methods"]["public"]["abstract"]["static"] as $i => $obj) {
                $resObj = self::$mainResultToArray["methods"]["public"]["abstract"]["static"][$i];

                $this->checkDocBlockArrayMainProperties($obj, $resObj);
                $this->checkDocBlockParameterProperties($obj, $resObj);

                $this->assertEquals($obj["return"], $resObj["return"]);
            }
            foreach (self::$mainExpectedToArray["methods"]["public"]["abstract"]["nonstatic"] as $i => $obj) {
                $resObj = self::$mainResultToArray["methods"]["public"]["abstract"]["nonstatic"][$i];

                $this->checkDocBlockArrayMainProperties($obj, $resObj);
                $this->checkDocBlockParameterProperties($obj, $resObj);

                $this->assertEquals($obj["return"], $resObj["return"]);
            }
            foreach (self::$mainExpectedToArray["methods"]["public"]["nonabstract"]["static"] as $i => $obj) {
                $resObj = self::$mainResultToArray["methods"]["public"]["nonabstract"]["static"][$i];

                $this->checkDocBlockArrayMainProperties($obj, $resObj);
                $this->checkDocBlockParameterProperties($obj, $resObj);

                $this->assertEquals($obj["return"], $resObj["return"]);
            }
            foreach (self::$mainExpectedToArray["methods"]["public"]["nonabstract"]["nonstatic"] as $i => $obj) {
                $resObj = self::$mainResultToArray["methods"]["public"]["nonabstract"]["nonstatic"][$i];

                $this->checkDocBlockArrayMainProperties($obj, $resObj);
                $this->checkDocBlockParameterProperties($obj, $resObj);

                $this->assertEquals($obj["return"], $resObj["return"]);
            }
        }
    }





    public function test_method_toArray_interfaces()
    {
        $this->setTestDirs();
        $interfaceExpected = file_get_contents($this->pathToTestJSONs . "/ExpectedObj02_iRealTypeTest.json");


        $obj = new ObjectDocumentation(
            $this->pathToTestClasses . "/iRealType.php",
            "AeonDigital\\Interfaces\\iRealType",
            ElementType::UNKNOW
        );


        $jsonData = \json_encode($obj->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        \file_put_contents($this->pathToTestJSONs . "/ResultObj02_iRealTypeTest.json", $jsonData);
        $this->assertEquals($interfaceExpected, $jsonData);
    }





    public function test_method_toArray_traits()
    {
        $this->setTestDirs();
        $interfaceExpected = file_get_contents($this->pathToTestJSONs . "/ExpectedObj03_FloatMethods.json");


        $obj = new ObjectDocumentation(
            $this->pathToTestClasses . "/FloatMethods.php",
            "AeonDigital\\SimpleTypes\\Traits\\FloatMethods",
            ElementType::UNKNOW
        );


        $jsonData = \json_encode($obj->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        \file_put_contents($this->pathToTestJSONs . "/ResultObj03_FloatMethods.json", $jsonData);
        $this->assertEquals($interfaceExpected, $jsonData);
    }





    public function test_method_toArray_enum()
    {
        $this->setTestDirs();
        $interfaceExpected = file_get_contents($this->pathToTestJSONs . "/ExpectedObj04_PrimitiveType.json");


        $obj = new ObjectDocumentation(
            $this->pathToTestClasses . "/PrimitiveType.php",
            "AeonDigital\\SimpleTypes\\Enums\\PrimitiveType",
            ElementType::UNKNOW
        );


        $jsonData = \json_encode($obj->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        \file_put_contents($this->pathToTestJSONs . "/ResultObj04_PrimitiveType.json", $jsonData);
        $this->assertEquals($interfaceExpected, $jsonData);
    }





    public function test_method_toArray_standalone_objects()
    {
        $this->setTestDirs();
        $interfaceExpected = file_get_contents($this->pathToTestJSONs . "/ExpectedObj05_StandaloneObjects.json");


        $obj = new ObjectDocumentation(
            $this->pathToTestClasses . "/StandaloneObjects.php",
            "",
            ElementType::UNKNOW
        );


        $jsonData = \json_encode($obj->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        \file_put_contents($this->pathToTestJSONs . "/ResultObj05_StandaloneObjects.json", $jsonData);
        $this->assertEquals($interfaceExpected, $jsonData);
    }
}