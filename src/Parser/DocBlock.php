<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Parser;









/**
 * Traz métodos para efetuar o tratamento dos DocBlock permitindo
 * convertê-los de strings em arrays associativos.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
class DocBlock
{



    /**
     * Remove todas as entradas vazias de um array até encontrar a o primeiro item
     * não vazio. Faz o mesmo de traz para frente para obter um array que contenha
     * apenas dados aproveitáveis.
     *
     * @param string[] $stringArray
     * Array de strings que será analisado.
     *
     * @return string[]
     */
    public static function trimArray(array $stringArray): array
    {
        $r = [];

        $isStartData = false;
        for ($i = 0; $i < \count($stringArray); $i++) {
            if ($isStartData === false && $stringArray[$i] !== "") {
                $isStartData = true;
            }

            if ($isStartData === true) {
                $r[] = $stringArray[$i];
            }
        }

        $isStartData = false;
        for ($i = (\count($r) - 1); $i >= 0; $i--) {
            if ($isStartData === false && $r[$i] !== "") {
                $isStartData = true;
            }

            if ($isStartData === false) {
                unset($r[$i]);
            }
        }

        return $r;
    }



    /**
     * Trata ums string que representa um DocBlock para remover os caracteres que
     * servem unicamente para sua marcação e retorna um array onde cada entrada
     * representa uma das linhas de dados encontrado.
     *
     * @param string $rawDocBlock
     * String original do DocBlock que será analisado.
     *
     * @return string[]
     */
    public static function parseRawDocBlockToRawLineArray(string $rawDocBlock): array
    {
        $r = [];
        $rawDocBlock = \trim($rawDocBlock);

        if ($rawDocBlock !== "") {
            $rawDocBlockLines = \explode("\n", $rawDocBlock);

            foreach ($rawDocBlockLines as $rawLine) {
                $tRawLine = \trim(\str_replace("*/", "", $rawLine));

                if (\str_starts_with($tRawLine, "/**") === true) {
                    $tRawLine = \trim(\substr($tRawLine, 3));
                    if ($tRawLine === "") {
                        $tRawLine = null;
                    }
                } elseif (\str_starts_with($tRawLine, "*") === true) {
                    $tRawLine = \trim(\substr($tRawLine, 1));
                }

                if ($tRawLine !== null) {
                    $r[] = $tRawLine;
                }
            }

            $r = self::trimArray($r);
        }

        return $r;
    }



    /**
     * A partir de um array de linhas de dados brutos de um DocBlock efetua o processamento
     * que permite identificar seu ``summary``, ``description`` e suas demais tags.
     *
     * @param array $rawLineArray
     * Array de linhas de dados brutos de um DocBlock.
     *
     * @return array
     * O array associativo terá a seguinte estrutura:
     * ```php
     *  $arr = [
     *      "summary" => [],
     *      "description" => [],
     *      "tags" => [
     *          "tagName01" => [
     *              [], [], []
     *          ]
     *          "tagName02" => [
     *              [], []
     *          ]
     *      ],
     *  ];
     * ```
     */
    public static function parseRawLineArrayToAssocArray(array $rawLineArray): array
    {
        $r = [
            "summary" => [],
            "description" => [],
            "tags" => [],
        ];

        if (\count($rawLineArray) > 0) {
            $isInTagsRegion = \str_starts_with("@", $rawLineArray[0]);
            $isInSummaryRegion = true;
            $tagName = null;
            $tagNameIndex = null;
            $tagNameLength = null;


            foreach ($rawLineArray as $rawLine) {
                if (
                    $isInTagsRegion === false &&
                    \str_starts_with($rawLine, "@") === true
                ) {
                    $isInTagsRegion = true;
                }

                if ($isInTagsRegion === false) {
                    if ($isInSummaryRegion === true) {
                        if ($rawLine === "") {
                            $isInSummaryRegion = false;
                        } else {
                            $r["summary"][] = $rawLine;
                        }
                    } else {
                        $r["description"][] = $rawLine;
                    }
                } else {
                    if (\str_starts_with($rawLine, "@") === true) {
                        $tagName = \explode(" ", \substr($rawLine, 1))[0];
                        $tagNameLength = \strlen("@" . $tagName);
                        $rawLine = \trim(substr($rawLine, $tagNameLength));

                        if (\key_exists($tagName, $r["tags"]) === false) {
                            $r["tags"][$tagName] = [];
                        }

                        $r["tags"][$tagName][] = [];
                        $tagNameIndex = (\count($r["tags"][$tagName]) - 1);
                    }

                    $r["tags"][$tagName][$tagNameIndex][] = $rawLine;
                }
            }


            $r["summary"] = self::trimArray($r["summary"]);
            $r["description"] = self::trimArray($r["description"]);

            foreach ($r["tags"] as $tagName => $rawTagsDocBlock) {
                foreach ($rawTagsDocBlock as $i => $rawTagDocBlock) {
                    $r["tags"][$tagName][$i] = self::trimArray($rawTagDocBlock);
                }
            }
        }

        return $r;
    }



    /**
     * A partir da coleção de linhas existentes para a descrição de um parametro de um método ou
     * função, retorna um array contendo 2 posições onde a primeira trará o nome do parametro
     * e o segundo trará uma nova coleção de linhas usando o mesmo tipo de array fornecido
     * pela função ``self::parseRawLineArrayToAssocArray()``.
     *
     * @param array $rawDocBlockParamLines
     * Arrau das linhas descritivas de um parametro de um método ou função.
     *
     * @return array
     */
    public static function parseRawDocBlockParamLines(array $rawDocBlockParamLines): array
    {
        $paramName = null;
        $parameterDocBlock = [];

        foreach ($rawDocBlockParamLines as $i => $rawParamLine) {
            if ($rawParamLine !== "" && $paramName === null) {
                $splitLine = \array_map("trim", \explode(" ", $rawParamLine));

                foreach ($splitLine as $part) {
                    if ($part !== "" && \str_starts_with($part, "\$") === true) {
                        $paramName = \substr($part, 1);
                        $splitParamLine = \array_map("trim", \explode("\$" . $paramName, $rawParamLine));
                        \array_shift($splitParamLine);

                        $rawParamLine = \implode("", $splitParamLine);
                        if ($rawParamLine === "") {
                            $rawParamLine = null;
                        }

                        break;
                    }
                }
            }

            if ($rawParamLine !== null) {
                $parameterDocBlock[] = $rawParamLine;
            }
        }

        return [$paramName, self::parseRawLineArrayToAssocArray($parameterDocBlock)];
    }
}