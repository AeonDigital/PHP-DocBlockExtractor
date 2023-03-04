<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\Parser\DocBlock as DocBlock;
use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;

require_once __DIR__ . "/../../phpunit.php";





class DocBlockTests extends TestCase
{


    static string $testMainDocBlock = "
        /**
         *
         *
         * This is a Summary.
         * Still summary.
         *
         * This is a Description. It may span multiple lines
         * or contain 'code' examples using the _Markdown_ markup
         * language.
         *
         * @see Markdown
         * @link https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md
         *       PHPFIG GitHub PHPDoc
         *
         * @param int        \$parameter1 A parameter description.
         * @param null|string \$parameter2
         * Description in second line.
         *
         * @param \DateTime \$de          Another parameter description.
         * ---
         * \$arr = [
         *     \"summary\" => [],
         *     \"description\" => [],
         *     \"tags\" => [
         *         \"tagName01\" => [
         *             [], [], []
         *         ]
         *         \"tagName02\" => [
         *             [], []
         *         ]
         *     ],
         * ];
         * ---
         *
         *
         * @\Doctrine\Orm\Mapper\Entity()
         *
         * @throws \Exception Exception description
         *
         * @return string
         *
         *
         * @package     AeonDigital\DocBlockExtractor
         * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
         * @copyright   2023, Rianna Cantarelli
         * @license     MIT
         *
         * @link https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc-tags.md
         *       PHPFIG GitHub PHPDoc Tags
         *
         *
         */";
    static string $singleLineDocBlock = "/** @var string \$varname   Varname description.   */";





    public function test_static_method_trimArray()
    {
        $originalObj = [
            "", "", "", "valid line", " ", "still valid", "", "", "", "last valid line", "", ""
        ];
        $expectedObj = [
            "valid line", " ", "still valid", "", "", "", "last valid line"
        ];
        $resultObj = DocBlock::trimArray($originalObj);


        $this->assertEquals(\count($expectedObj), \count($resultObj));
        for ($i = 0; $i < \count($resultObj); $i++) {
            $this->assertEquals($expectedObj[$i], $resultObj[$i]);
        }
    }



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





    public function test_static_method_parseObjectDeclaration()
    {
        $originalObj = [
            "const CStandalone = [\"array\"];",
            "\$VStandalone = \"test\";",
            "function array_check_required_keys(array \$keys, array \$array): array",
        ];
        $expectedObj = [
            [
                "type" => ElementType::CONSTANT->value,
                "shortName" => "CStandalone"
            ],
            [
                "type" => ElementType::VARIABLE->value,
                "shortName" => "VStandalone"
            ],
            [
                "type" => ElementType::FUNCTION->value,
                "shortName" => "array_check_required_keys"
            ],
        ];


        foreach ($originalObj as $i => $rawLine) {
            $oResult = DocBlock::parseObjectDeclaration($rawLine);

            $this->assertNotNull($oResult);
            $this->assertEquals($expectedObj[$i]["type"], $oResult["type"]);
            $this->assertEquals($expectedObj[$i]["shortName"], $oResult["shortName"]);
        }
    }



    public function test_static_method_parseStandaloneFileToMetaObjects()
    {
        $pathToStandaloneObjects = realpath(__DIR__ . "/../../resources/StandaloneObjects.php");

        $expectedObj = [
            "fileName" => "/var/www/html/tests/resources/StandaloneObjects.php",
            "namespaceName" => "AeonDigital\Standalone",
            "objects" => [
                [
                    "type" => "CONSTANT",
                    "shortName" => "CStandalone",
                    "docBlock" => [
                        "/**",
                        "* Constante de teste",
                        "*",
                        "* @var array CStandalone",
                        "*/"
                    ]
                ],
                [
                    "type" => "VARIABLE",
                    "shortName" => "VStandalone",
                    "docBlock" => [
                        "/** @var string \$VStandalone Variável de teste */"
                    ]
                ],
                [
                    "type" => "FUNCTION",
                    "shortName" => "array_check_required_keys",
                    "docBlock" => [
                        "/**",
                        "* Verifica se as chaves definidas como obrigatórias de um ``Array Associativo`` estão realmente",
                        "* presentes.",
                        "*",
                        "* @param array \$keys",
                        "* Coleção com o nome das chaves obrigatórias.",
                        "*",
                        "* @param array \$array",
                        "* ``Array associativo`` que será verificado.",
                        "*",
                        "* @return array",
                        "* Retorna um ``array`` contendo o nome de cada um dos itens que **NÃO** foram definidos.",
                        "* Ou seja, se retornar um ``array`` vazio, significa que todas as chaves foram definidas.",
                        "*/"
                    ]
                ]
            ]
        ];

        $resultObj = DocBlock::parseStandaloneFileToMetaObjects($pathToStandaloneObjects);

        $this->assertEquals($expectedObj["fileName"], $resultObj["fileName"]);
        $this->assertEquals($expectedObj["namespaceName"], $resultObj["namespaceName"]);

        $this->assertEquals(\count($expectedObj["objects"]), \count($resultObj["objects"]));
        foreach ($expectedObj["objects"] as $i => $metaObject) {
            $this->assertEquals($metaObject["type"], $resultObj["objects"][$i]["type"]);
            $this->assertEquals($metaObject["shortName"], $resultObj["objects"][$i]["shortName"]);
            $this->assertEquals($metaObject["docBlock"], $resultObj["objects"][$i]["docBlock"]);
        }
    }
}