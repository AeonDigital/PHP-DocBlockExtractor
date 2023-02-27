<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Functions;




/**
 * Efetua o processamento de uma linha que deve conter a declaração de uma
 * variável, constante ou função.
 *
 * @param string $declarationLine
 * Linha que será verificada.
 *
 * @return ?array
 * Retornará ``null`` se não for possível identificar o objeto.
 * Retorna um array unidimensional com 2 itens. Na posição 0 estará
 * a identificação do tipo de objeto. É esperado um dos seguintes valores:
 * - VARIABLE
 * - CONSTANT
 * - FUNCTION
 * Na posição 1 estará o nome do objeto encontrado.
 */
function parseObjectDeclaration(string $declarationLine): ?array
{
    $r = null;
    $tline = \trim($declarationLine);

    if (\str_starts_with($tline, "\$") === true) {
        $oName = \str_replace(["\$", ";", " "], "", $tline);
        $oName = \explode("=", $oName)[0];

        $r = ["VARIABLE", $oName];
    } elseif (\str_starts_with($tline, "const ") === true) {
        $oName = \substr($tline, 6);
        $oName = \explode(" ", \trim(\explode("=", $oName)[0]));
        $oName = $oName[count($oName) - 1];

        $r = ["CONSTANT", $oName];
    } elseif (\str_starts_with($tline, "function ") === true) {
        $oName = \substr($tline, 9);
        $oName = \trim(\explode("(", $oName)[0]);

        $r = ["FUNCTION", $oName];
    }

    return $r;
}