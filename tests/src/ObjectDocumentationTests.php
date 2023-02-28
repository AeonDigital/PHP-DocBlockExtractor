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



    public function test_method_toArray()
    {
        $pathToClassTest = realpath(__DIR__ . "/../resources/DocumentationClassTest.php");
        $fqsen = "AeonDigital\\DocBlockExtractor\\Tests\\DocumentationClassTest";

        $obj = new ObjectDocumentation(
            $pathToClassTest,
            $fqsen,
            ElementType::UNKNOW
        );


        $expectedObj = [
            "fileName"      => $pathToClassTest,
            "namespaceName" => "AeonDigital\\DocBlockExtractor\\Tests",
            "fqsen"         => $fqsen,
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

            /*"constructor"       => null,

            "constants"         => [],

            "staticProperties"  => [],
            "publicProperties"  => [],

            "staticMethods"     => [],
            "abstractMethods"   => [],
            "publicMethods"     => []*/
        ];

        $resultObj = $obj->toArray();
        $this->checkRecursiveValues($expectedObj, $resultObj);
    }



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


    /*
    public function test_static_method_parseRawDocBlockToRawLineArray()
    {
        $originalObj = self::$testMainDocBlock;
        $expectedObj = [
            "This is a Summary.",
            "Still summary.",
            "",
            "This is a Description. It may span multiple lines",
            "or contain 'code' examples using the _Markdown_ markup",
            "language.",
            "",
            "@see Markdown",
            "@link https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md",
            "PHPFIG GitHub PHPDoc",
            "",
            "@param int        \$parameter1 A parameter description.",
            "@param null|string \$parameter2",
            "Description in second line.",
            "",
            "@param \DateTime \$de          Another parameter description.",
            "---",
            "\$arr = [",
            "    \"summary\" => [],",
            "    \"description\" => [],",
            "    \"tags\" => [",
            "        \"tagName01\" => [",
            "            [], [], []",
            "        ]",
            "        \"tagName02\" => [",
            "            [], []",
            "        ]",
            "    ],",
            "];",
            "---",
            "",
            "",
            "@\Doctrine\Orm\Mapper\Entity()",
            "",
            "@throws \Exception Exception description",
            "",
            "@return string",
            "",
            "",
            "@package     AeonDigital\DocBlockExtractor",
            "@author      Rianna Cantarelli <rianna@aeondigital.com.br>",
            "@copyright   2023, Rianna Cantarelli",
            "@license     MIT",
            "",
            "@link https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc-tags.md",
            "PHPFIG GitHub PHPDoc Tags",
        ];
        $resultObj = DocBlock::parseRawDocBlockToRawLineArray($originalObj);

        $this->assertEquals(\count($expectedObj), \count($resultObj));
        for ($i = 0; $i < \count($resultObj); $i++) {
            $this->assertEquals($expectedObj[$i], $resultObj[$i]);
        }



        $originalObj = self::$singleLineDocBlock;
        $expectedObj = [
            "@var string \$varname   Varname description."
        ];
        $resultObj = DocBlock::parseRawDocBlockToRawLineArray($originalObj);


        $this->assertEquals(\count($expectedObj), \count($resultObj));
        for ($i = 0; $i < \count($resultObj); $i++) {
            $this->assertEquals($expectedObj[$i], $resultObj[$i]);
        }
    }



    public function test_static_method_parseRawLineArrayToAssocArray()
    {
        $originalObj = self::$testMainDocBlock;
        $expectedObj = [
            "summary" => [
                "This is a Summary.",
                "Still summary."
            ],
            "description" => [
                "This is a Description. It may span multiple lines",
                "or contain 'code' examples using the _Markdown_ markup",
                "language."
            ],
            "tags" => [
                "see" => [
                    [
                        "Markdown"
                    ]
                ],
                "link" => [
                    [
                        "https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md",
                        "PHPFIG GitHub PHPDoc",
                    ],
                    [
                        "https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc-tags.md",
                        "PHPFIG GitHub PHPDoc Tags",
                    ]
                ],
                "param" => [
                    [
                        "int        \$parameter1 A parameter description."
                    ],
                    [
                        "null|string \$parameter2",
                        "Description in second line.",
                    ],
                    [
                        "\DateTime \$de          Another parameter description.",
                        "---",
                        "\$arr = [",
                        "    \"summary\" => [],",
                        "    \"description\" => [],",
                        "    \"tags\" => [",
                        "        \"tagName01\" => [",
                        "            [], [], []",
                        "        ]",
                        "        \"tagName02\" => [",
                        "            [], []",
                        "        ]",
                        "    ],",
                        "];",
                        "---",
                    ]
                ],
                "\Doctrine\Orm\Mapper\Entity()" => [],
                "throws" => [
                    [
                        "\Exception Exception description"
                    ]
                ],
                "return" => [
                    [
                        "string"
                    ]
                ],
                "package" => [
                    [
                        "AeonDigital\DocBlockExtractor"
                    ]
                ],
                "author" => [
                    [
                        "Rianna Cantarelli <rianna@aeondigital.com.br>"
                    ]
                ],
                "copyright" => [
                    [
                        "2023, Rianna Cantarelli"
                    ]
                ],
                "license" => [
                    [
                        "MIT"
                    ]
                ]
            ],
        ];
        $rawLineArray = DocBlock::parseRawDocBlockToRawLineArray($originalObj);
        $resultObj = DocBlock::parseRawLineArrayToAssocArray($rawLineArray);


        foreach ($expectedObj as $key => $value) {
            $this->assertTrue(\key_exists($key, $resultObj));
        }
        $this->assertEquals($expectedObj["summary"], $resultObj["summary"]);
        $this->assertEquals($expectedObj["description"], $resultObj["description"]);


        foreach (\array_keys($expectedObj["tags"]) as $tagName) {
            $this->assertTrue(\key_exists($tagName, $resultObj["tags"]));
        }

        foreach ($expectedObj["tags"] as $tagName => $tagsRawDocBlock) {
            foreach ($tagsRawDocBlock as $i => $tagRawDocBlock) {
                $this->assertTrue(isset($resultObj["tags"][$tagName][$i]));
                $this->assertEquals($expectedObj["tags"][$tagName][$i], $resultObj["tags"][$tagName][$i]);
            }
        }
    }



    public function test_static_method_parseRawDocBlockParamLines()
    {
        $originalObj = [
            "int        \$parameter1 A parameter description."
        ];
        $expectedObj = [
            "parameter1",
            [
                "summary" => [
                    "A parameter description.",
                ],
                "description" => [],
                "tags" => []
            ]
        ];
        $resultObj = DocBlock::parseRawDocBlockParamLines($originalObj);

        $this->assertEquals(2, \count($resultObj));
        $this->assertEquals($expectedObj[0], $resultObj[0]);
        $this->assertEquals($expectedObj[1]["summary"], $resultObj[1]["summary"]);



        $originalObj = [
            "null|string \$parameter2",
            "Description in second line.",
        ];
        $expectedObj = [
            "parameter2",
            [
                "summary" => [
                    "Description in second line.",
                ],
                "description" => [],
                "tags" => []
            ]
        ];
        $resultObj = DocBlock::parseRawDocBlockParamLines($originalObj);

        $this->assertEquals(2, \count($resultObj));
        $this->assertEquals($expectedObj[0], $resultObj[0]);
        $this->assertEquals($expectedObj[1]["summary"], $resultObj[1]["summary"]);



        $originalObj = [
            "null|string \$parameter3",
            "Description in second line.",
            "   And another line",
            "",
            "And here a description."
        ];
        $expectedObj = [
            "parameter3",
            [
                "summary" => [
                    "Description in second line.",
                    "   And another line"
                ],
                "description" => [
                    "And here a description."
                ],
                "tags" => []
            ]
        ];
        $resultObj = DocBlock::parseRawDocBlockParamLines($originalObj);

        $this->assertEquals(2, \count($resultObj));
        $this->assertEquals($expectedObj[0], $resultObj[0]);
        $this->assertEquals($expectedObj[1]["summary"], $resultObj[1]["summary"]);
    }
    */
}