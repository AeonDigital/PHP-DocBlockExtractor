<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Functions;




/**
 * Efetua o processamento de um arquivo avulso linha a linha em busca dos
 * objetos que estão documentados usando DocBlocks.
 *
 * @param string $detachedFile
 * Caminho completo até o arquivo que será verificado
 *
 * @return array
 * Retorna um array associativo contendo 4 chaves:
 * - namespace
 * - VARIABLE
 * - CONSTANT
 * - FUNCTION
 * Exceto para "namespaces", nas demais chaves virão um array unidimensional
 * contendo o nome de cada um dos objetos encontrados.
 */
function parseDetachedFile(string $detachedFile): array
{
    $r = [
        "namespace" => "",
        "VARIABLE" => [],
        "CONSTANT" => [],
        "FUNCTION" => []
    ];

    $fileContent = \explode("\n", \file_get_contents($detachedFile));
    $insideDocBlock = false;
    $isToGetNextLine = false;
    foreach ($fileContent as $line) {
        $tline = \trim($line);
        if ($tline !== "") {
            if ($r["namespace"] === "" && \str_starts_with($tline, "namespace ") === true) {
                $r["namespace"] = \str_replace(";", "", \substr($tline, 10));
            }

            if ($insideDocBlock === false && \str_starts_with($tline, "/**") === true) {
                $insideDocBlock = true;
                if (\str_ends_with($tline, "*/") === true) {
                    $isToGetNextLine = true;
                    $insideDocBlock = false;
                }
            } elseif ($insideDocBlock === true && \str_ends_with($tline, "*/") === true) {
                $isToGetNextLine = true;
                $insideDocBlock = false;
            } elseif ($isToGetNextLine === true) {
                $isToGetNextLine = false;

                $objectData = parseObjectDeclaration($tline);
                if ($objectData !== null) {
                    $t = $objectData[0];
                    $o = $objectData[1];
                    $r[$t][] = $o;
                }
            }
        }
    }

    return $r;
}