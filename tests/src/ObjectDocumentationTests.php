<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\ObjectDocumentation as ObjectDocumentation;
use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;

require_once __DIR__ . "/../phpunit.php";





class ObjectDocumentationTests extends TestCase
{




    public function test_constructor_fails_fileNotFound()
    {
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
    }



    public function test_constructor_ok()
    {
        $pathToClassTest = realpath(__DIR__ . "/../resources/DocumentationClassTest.php");
        $obj = new ObjectDocumentation(
            $pathToClassTest,
            "AeonDigital\\DocBlockExtractor\\Tests\\DocumentationClassTest",
            ElementType::UNKNOW
        );
        $this->assertTrue(is_a($obj, ObjectDocumentation::class));
    }



    public function test_get_properties()
    {
        $pathToClassTest = realpath(__DIR__ . "/../resources/DocumentationClassTest.php");
        $fqsen = "AeonDigital\\DocBlockExtractor\\Tests\\DocumentationClassTest";

        $obj = new ObjectDocumentation(
            $pathToClassTest,
            $fqsen,
            ElementType::UNKNOW
        );


        $this->assertEquals($pathToClassTest, $obj->getFileName());
        $this->assertEquals("AeonDigital\\DocBlockExtractor\\Tests", $obj->getNamespaceName());
        $this->assertEquals($fqsen, $obj->getFQSEN());
        $this->assertEquals("DocumentationClassTest", $obj->getShortName());
        $this->assertEquals(ElementType::CLASSE, $obj->getType());
    }



    static array $mainExpectedToArray = [
        "fileName"      => "/var/www/html/tests/resources/DocumentationClassTest.php",
        "namespaceName" => "AeonDigital\\DocBlockExtractor\\Tests",
        "fqsen"         => "AeonDigital\\DocBlockExtractor\\Tests\\DocumentationClassTest",
        "shortName"     => "DocumentationClassTest",
        "type"          => ElementType::CLASSE->value,

        "docBlock"      => [
            "summary" => [
                "Classe fake para teste de extração de documentação.",
            ],
            "description" => [],
            "tags" => [
                "package" => [
                    ["AeonDigital\DocBlockExtractor"]
                ],
                "author" => [
                    ["Rianna Cantarelli <rianna@aeondigital.com.br>"]
                ],
                "copyright" => [
                    ["2023, Rianna Cantarelli"]
                ],
                "license" => [
                    ["MIT"]
                ],
            ],
        ],

        "interfaces"    => null,
        "extends"       => null,

        "isAbstract"    => false,
        "isFinal"       => false,

        "constants"     => [
            [
                "fileName" => "/var/www/html/tests/resources/DocumentationClassTest.php",
                "namespaceName" => "AeonDigital\DocBlockExtractor\Tests\DocumentationClassTest",
                "fqsen" => "AeonDigital\DocBlockExtractor\Tests\DocumentationClassTest\PUB_CONST_01",
                "shortName" => "PUB_CONST_01",
                "type" => "CONSTANT",
                "docBlock" => [
                    "summary" => [""],
                    "description" => [""],
                    "tags" => [
                        "var" => [
                            ["int PUB_CONST_01 Uma constante de teste."]
                        ]
                    ]
                ]
            ],
            [
                "fileName" => "/var/www/html/tests/resources/DocumentationClassTest.php",
                "namespaceName" => "AeonDigital\DocBlockExtractor\Tests\DocumentationClassTest",
                "fqsen" => "AeonDigital\DocBlockExtractor\Tests\DocumentationClassTest\PUB_CONST_02",
                "shortName" => "PUB_CONST_02",
                "type" => "CONSTANT",
                "docBlock" => [
                    "summary" => ["Outra constante de teste."],
                    "description" => ["Descrição mais abaixo"],
                    "tags" => [
                        "var" => [
                            [""]
                        ]
                    ]
                ]
            ]
        ]
        /*"constructor"       => null,


        "staticProperties"  => [],
        "publicProperties"  => [],

        "staticMethods"     => [],
        "abstractMethods"   => [],
        "publicMethods"     => []*/
    ];
    static ?array $mainResultToArray = null;




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




    public function test_method_toArray()
    {
        $pathToClassTest = realpath(__DIR__ . "/../resources/DocumentationClassTest.php");
        $fqsen = "AeonDigital\\DocBlockExtractor\\Tests\\DocumentationClassTest";

        self::$mainExpectedToArray["fileName"] = $pathToClassTest;

        $obj = new ObjectDocumentation(
            $pathToClassTest,
            $fqsen,
            ElementType::UNKNOW
        );


        self::$mainResultToArray = $obj->toArray();
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
                \count(self::$mainExpectedToArray["constants"]),
                \count(self::$mainResultToArray["constants"])
            );

            foreach (self::$mainExpectedToArray["constants"] as $i => $obj) {
                $this->checkDocBlockArrayMainProperties(
                    $obj,
                    self::$mainExpectedToArray["constants"][$i]
                );
            }
        }
    }












    /*
    public function checkRecursiveValues($expectedObj, $resultObj)
    {
        if ($this->arrayIsAssoc($expectedObj) === true) {
            $this->assertTrue($this->arrayIsAssoc($resultObj));

            foreach (\array_keys($expectedObj) as $key) {
                $this->assertTrue(\key_exists($key, $resultObj));
                $this->checkRecursiveValues($expectedObj[$key], $resultObj[$key]);
            }
        } elseif (\is_array($expectedObj) === true) {
            $this->assertTrue(\is_array($resultObj));
            $this->assertFalse($this->arrayIsAssoc($resultObj));
            $this->assertEquals(\count($expectedObj), \count($resultObj));

            foreach ($expectedObj as $i => $childExpected) {
                $this->checkRecursiveValues($childExpected, $resultObj[$i]);
            }
        } else {
            $this->assertEquals($expectedObj, $resultObj);
        }
    }



    public function arrayIsAssoc($o): bool
    {
        if (\is_array($o) === true && $o !== []) {
            return \array_keys($o) !== \range(0, \count($o) - 1);
        }
        return false;
    }
    */
}