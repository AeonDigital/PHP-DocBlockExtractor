<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Functions;

use AeonDigital\DocBlockExtractor\DocBlockExtractor as DocBlockExtractor;


/**
 * Processa os arquivos do projeto e extrai um objeto ``DocBlockExtractor`` devidamente
 * preenchido.
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
 * @return DocBlockExtractor
 */
function retrieveProjectMainData(
    string $vendorDir,
    array $detachedFilesAndDirectories = [],
    array $ignoreList = []
): DocBlockExtractor {
    $projectData = new DocBlockExtractor();


    //
    // Inicia a coleta dos objetos do projeto a partir daqueles que estão mapeados pelo composer.
    // Neste momento interfaces, enumeradores, traits, e classes serão coletadas e registradas.
    $projectObjects = retrieveProjectDataMap($vendorDir);

    //
    // Identifica os arquivos avulsos que devem ser carregados com o projeto e a partir deles,
    // identifica variáveis, constantes e funções que estão dispersas e por algum motivo não
    // foram mapeadas.
    $detachedObjects = retrieveDetachedDataMap($vendorDir, $detachedFilesAndDirectories);
    foreach ($detachedObjects as $fqn => $v) {
        $projectObjects[$fqn] = $v;
    }
    unset($detachedObjects);




    /** @var AeonDigital\DocBlockExtractor\DataObject $dataObject */
    foreach ($projectObjects as $fqn => $dataObject) {
        $projectData->addObject($dataObject);
    }


    return $projectData;
}