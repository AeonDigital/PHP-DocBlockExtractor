<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Functions;

use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;
use AeonDigital\DocBlockExtractor\DataObject as DataObject;

/**
 * Processa a lista de arquivos avulsos que fazem parte do projeto.
 *
 * Estes arquivos podem estar registrados na sessão "autoload:files" do arquivo "composer.json"
 * ou ainda serem carregados dinamicamente em algum ponto do código do projeto. Por estes motivos
 * não fazem parte do mapeamento padrão.
 *
 * @param string $vendorDir
 * Caminho completo até o diretório ``vendor`` do projeto atual.
 *
 * @param string[] $detachedFilesAndDirectories
 * Coleção de caminhos completos até arquivos ou diretórios que possuem arquivos avulsos e
 * que são carregados dinamicamente.
 *
 * @param string[] $ignoreList
 * Lista de arquivos ou diretórios que devem ser ignorados do processamento.
 *
 * @return array
 * Array associativo contendo os itens pertencentes à este projeto.
 * Nas chaves virá o FQN de cada objeto e associada a ela estará um objeto
 * do tipo ``DataObject``.
 */
function retrieveDetachedDataMap(
    string $vendorDir,
    array $detachedFilesAndDirectories = [],
    array $ignoreList = []
): array {
    $r = [];


    // Pega a lista de arquivos registrados pelo composer
    $detachedFiles = \array_values(include $vendorDir . "/composer/autoload_files.php");

    // Remove da lista de arquivos avulsos todos os itens que vem de outros projetos.
    for ($i = (\count($detachedFiles) - 1); $i >= 0; $i--) {
        if (\str_starts_with($detachedFiles[$i], $vendorDir) === true) {
            unset($detachedFiles[$i]);
        }
    }


    // Adiciona a lista de arquivos que são carregados dinamicamente.
    foreach ($detachedFilesAndDirectories as $targetPath) {
        if (\is_file($targetPath) === true) {
            $detachedFiles[] = $targetPath;
        } elseif (\is_dir($targetPath) === true) {
            array_push($detachedFiles, ...(dir_r_scan_w($targetPath)));
        }
    }


    // Remove da lista os itens indicados para serem ignorados.
    for ($i = (\count($detachedFiles) - 1); $i >= 0; $i--) {
        foreach ($ignoreList as $targetIgnore) {
            if (
                $targetIgnore === $detachedFiles[$i] ||
                \str_starts_with($detachedFiles[$i], $targetIgnore . "\\")
            ) {
                unset($detachedFiles[$i]);
            }
        }
    }


    foreach ($detachedFiles as $targetFile) {
        $fileObjects = parseDetachedFile($targetFile);

        $useNamespace = $fileObjects["namespace"];
        unset($fileObjects["namespace"]);

        foreach ($fileObjects as $type => $objectNames) {
            foreach ($objectNames as $oName) {
                $fqn = (($useNamespace === "") ? "" : $useNamespace . "\\") . $oName;
                $r[$fqn] = new DataObject(
                    $targetFile,
                    $fqn,
                    ElementType::from($type)
                );
            }
        }
    }

    return $r;
}