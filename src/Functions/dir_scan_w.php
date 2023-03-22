<?php

declare(strict_types=1);






/**
 * Retorna a listagem do conteúdo do diretório alvo já ordenado adequadamente conforme o
 * padrão Windows.
 *
 * @param string $absoluteSystemPathToDir
 * Diretório que será listado.
 *
 * @codeCoverageIgnore
 * Teste coberto no projeto ``PHP-Core`` na função ``dir_scan_w``.
 * Função foi portada para cá para tornar este projeto o mais independente possível.
 *
 * @return array
 * Lista de diretórios e arquivos encontrados no local indicado.
 */
function dir_scan_w(string $absoluteSystemPathToDir): array
{
    $dirContent = \scandir($absoluteSystemPathToDir);

    $tgtDirs = [];
    $tgtDotFiles = [];
    $tgtUnderFiles = [];
    $tgtFiles = [];

    foreach ($dirContent as $tgtName) {
        if (
            $tgtName === "." ||
            $tgtName === ".." ||
            \is_dir($absoluteSystemPathToDir . DIRECTORY_SEPARATOR . $tgtName) === true
        ) {
            $tgtDirs[] = $tgtName;
        } else {
            if ($tgtName[0] === ".") {
                $tgtDotFiles[] = $tgtName;
            } else {
                if ($tgtName[0] === "_") {
                    $tgtUnderFiles[] = $tgtName;
                } else {
                    $tgtFiles[] = $tgtName;
                }
            }
        }
    }

    // Reordena os itens e refaz o index dos elementos.
    \natcasesort($tgtDirs);
    \natcasesort($tgtDotFiles);
    \natcasesort($tgtUnderFiles);
    \natcasesort($tgtFiles);

    $dirContent = \array_merge($tgtDirs, $tgtDotFiles, $tgtUnderFiles, $tgtFiles);

    return $dirContent;
}