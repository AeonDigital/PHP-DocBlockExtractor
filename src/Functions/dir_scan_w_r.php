<?php

declare(strict_types=1);






/**
 * Retorna um array contendo o caminho completo para todos os arquivos dentro do diretório
 * alvo. Esta ação é recursiva.
 *
 * @param string $absoluteSystemPathToDir
 * Diretório que será listado.
 *
 * @codeCoverageIgnore
 * Teste coberto no projeto ``PHP-Core`` na função ``dir_scan_w_r``.
 * Função foi portada para cá para tornar este projeto o mais independente possível.
 *
 * @return string[]
 * Lista de arquivos encontrados no local indicado.
 */
function dir_scan_w_r(string $absoluteSystemPathToDir): array
{
    $r = [];
    $dirContent = \dir_scan_w($absoluteSystemPathToDir);
    foreach ($dirContent as $tgtName) {
        if ($tgtName !== "." && $tgtName !== "..") {
            $fullPath = $absoluteSystemPathToDir . DIRECTORY_SEPARATOR . $tgtName;
            if (\is_dir($fullPath) === true) {
                $r = \array_merge($r, \dir_scan_w_r($fullPath));
            } else {
                $r[] = $fullPath;
            }
        }
    }
    return $r;
}