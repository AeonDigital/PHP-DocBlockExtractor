<?php

declare(strict_types=1);

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);


/*
 * IMPORTANTE
 *
 * Para a correta extração da documentação todos os objetos alvo devem
 * estar devidamente classificados pelo composer pois serão usado os arquivos
 * de mapeamento do mesmo para identificar os itens que pertencem exclusivamente
 * ao projeto atual.
 *
 *
 * A identificação de classes, interfaces, traits, métodos e propriedades é feito
 * a partir do mapeamento de classes criado pelo próprio composer. A partir dele
 * os objetos passarão por análise com Reflection onde seus membros dados serão
 * identificados e extraidos.
 *
 * Já os objetos que são iniciados por arquivos avulsos e não estão no mapeamento
 * feito pelo composer serão identificados por processamento que se dará seguindo
 * as seguintes orientações:
 * - Cada arquivo avulso será percorrido linha a linha em busca de DocBlocks.
 * - Na linha imediatamente abaixo do final de cada bloco deverá estar a definição
 *   de variável, constante ou função.
 * - Esta linha será processada em busca do nome do objeto.
 * - Será a partir do nome deste objeto que a análise de Reflection será feita para
 *   pegar os dados destes itens.
 *
 * Objetos que não tenham descrição por um DocBlock válido não será registrado.
 */




// Pega o caminho completo até o diretório 'vendor' e inicia o autoload do composer
$vendorDir = \dirname(\dirname(\dirname(\dirname(__FILE__))));
$rootDir = \dirname($vendorDir);
require_once $vendorDir . "/autoload.php";

// --------- --------- --------- --------- --------- ---------
// --------- --------- TEMPORÁRIO -------- --------- ---------
require_once "extractFunctions.php";
require_once "DocBlockExtractor/FileNotFoundException.php";
require_once "DocBlockExtractor/ProjectData.php";
require_once "DocBlockExtractor/DataObject.php";
require_once "DocBlockExtractor/ElementType.php";
require_once "DocBlockExtractor/DocBlock.php";

//$detachedDirs = [$rootDir . "/src/global_functions"];
$detachedDirs = [];
// --------- --------- --------- --------- --------- ---------



// Seguir com a exportação... começar por constantes!

//
// Objeto que traz todos os elementos do projeto.
$mainDataProject = retrieveProjectMainData($vendorDir, $detachedDirs);
$mainDataProjectArray = $mainDataProject->toArray();
var_dump($mainDataProjectArray);
//$rawData = $mainDataProject->getClasses()["AeonDigital"][0]->toArray();
//$jsonData = json_encode($rawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
//file_put_contents($rootDir . "/src/output.txt", $jsonData);
//var_dump($mainDataProject->getClasses()["AeonDigital"][0]->toArray());


    // https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md
    // https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc-tags.md