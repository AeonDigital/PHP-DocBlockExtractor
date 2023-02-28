<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\Parser\DocBlock as DocBlock;


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
}