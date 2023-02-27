<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Functions;




/**
 * Retorna um array contendo o caminho completo para todos  os
 * arquivos dentro do diretório alvo. Esta ação é recursiva.
 *
 * @param string $absoluteSystemPathToDir
 * Diretório que será listado.
 *
 * @return string[]
 * Lista de arquivos encontrados no local indicado.
 */
function dir_r_scan_w(string $absoluteSystemPathToDir): array
{
    $r = [];
    $dirContent = \dir_scan_w($absoluteSystemPathToDir);
    foreach ($dirContent as $tgtName) {
        if ($tgtName !== "." && $tgtName !== "..") {
            $fullPath = $absoluteSystemPathToDir . DS . $tgtName;
            if (\is_dir($fullPath) === true) {
                $r = \array_merge($r, \dir_r_scan_w($fullPath));
            } else {
                $r[] = $fullPath;
            }
        }
    }
    return $r;
}