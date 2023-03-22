<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\OutputExtractor;

use AeonDigital\DocBlockExtractor\OutputExtractor\aOutputExtractor as aOutputExtractor;
use AeonDigital\DocBlockExtractor\Exceptions\DirectoryNotFoundException as DirectoryNotFoundException;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;





/**
 * Efetua a extração dos dados de uma classe ``ProjectDocumentation`` em
 * um ou mais arquivos JSON.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
class JSON extends aOutputExtractor
{



    /**
     * Extrai a documentação para o formato implementado.
     *
     * @param ProjectDocumentation $proDoc
     * Instância a partir da qual a documentação será obtida.
     *
     * @param string $outputDir
     * Caminho completo até um diretório que será usado como repositório dos arquivos
     * criados. O conteúdo original deste diretório será eliminado antes de gerar a nova
     * documentação.
     *
     * @param bool $singleFile
     * Quando ``true`` o conteúdo será extraido para um único arquivo chamado ``index.json``.
     * Se ``false``, cada namespace representará um diretório dentro de ``$outputDir`` e
     * dentro de cada um serão alocados os seguintes arquivos/diretórios:
     *
     * - constants.json     [ 1 arquivo para todas as constantes da namespace ]
     * - variables.json     [ 1 arquivo para todas as variáveis da namespace ]
     * - functions          [ 1 diretório contendo 1 arquivo para cada função da namespace ]
     * - interfaces         [ 1 diretório contendo 1 arquivo para cada interface da namespace ]
     * - enuns              [ 1 diretório contendo 1 arquivo para cada enumerador da namespace ]
     * - traits             [ 1 diretório contendo 1 arquivo para cada trait da namespace ]
     * - classes            [ 1 diretório contendo 1 arquivo para cada classe da namespace ]
     *
     * @throws DirectoryNotFoundException
     * Caso o diretório ``outputDir`` indicado não exista.
     *
     * @throws RuntimeException
     * Caso não seja possível excluir totalmente o conteúdo do diretório ``$outputDir``.
     *
     * @return bool
     * Retorna ``true`` caso todos os arquivos o processo tenha corrido até o fim.
     */
    public function extract(
        ProjectDocumentation $proDoc,
        string $outputDir,
        bool $singleFile
    ): bool {
        $r = false;

        $structuredDocumentation = $this->prepareExtraction(
            $proDoc,
            $outputDir
        );

        if ($singleFile === true) {
            $r = $this->saveJSONFile(
                $outputDir . DIRECTORY_SEPARATOR . "index.json",
                $structuredDocumentation
            );
        } else {
            $r = true;

            foreach ($structuredDocumentation as $namespaceName => $namespaceComponents) {
                if ($r === true) {
                    $namespaceNamePath = $outputDir . DIRECTORY_SEPARATOR . \str_replace("\\", "_", $namespaceName);

                    $r = \mkdir($namespaceNamePath);
                    if ($r === true) {

                        foreach ($namespaceComponents as $componentType => $componentObjects) {
                            if ($r === true && $componentObjects !== []) {
                                switch ($componentType) {
                                    case "constants":
                                    case "variables":
                                        $r = $this->saveJSONFile(
                                            $namespaceNamePath . DIRECTORY_SEPARATOR . $componentType . ".json",
                                            $componentObjects
                                        );
                                        break;

                                    case "functions":
                                    case "interfaces":
                                    case "enuns":
                                    case "traits":
                                    case "classes":
                                        $r = $this->saveJSONComponentsFiles(
                                            $namespaceNamePath . DIRECTORY_SEPARATOR . $componentType,
                                            $componentObjects
                                        );
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $r;
    }




    /**
     * Salva os dados passados para um arquivo.
     *
     * @param string $absolutePathToFile
     * Caminho completo até o local onde o novo arquivo será criado.
     *
     * @param array $data
     * Informações que serão salvas no arquivo.
     *
     * @return bool
     * Retorna ``true`` se o arquivo for salvo corretamente.
     */
    protected function saveJSONFile(
        string $absolutePathToFile,
        array $data
    ): bool {
        $jsonData = \json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $r = \file_put_contents($absolutePathToFile, $jsonData);
        return (($r === false) ? false : true);
    }
    /**
     * Salva os dados passados para arquivos individualizados sendo 1 para cada
     * componente listado. Os arquivos terão o mesmo nome indicado na chave ``shortName`` de
     * suas respectivas descrições.
     *
     * @param string $componentsPath
     * Caminho completo até o diretório onde os novos arquivos serão gerados.
     *
     * @param array $componentObjects
     * Array contendo cada um dos componentes que deve ser individualizado em um arquivo a parte.
     *
     * @return bool
     * Retorna ``true`` se todos os arquivos forem salvos corretamente.
     */
    protected function saveJSONComponentsFiles(
        string $componentsPath,
        array $componentObjects
    ): bool {
        $r = \mkdir($componentsPath);

        if ($r === true) {
            foreach ($componentObjects as $componentData) {
                if ($r === true) {
                    $r = $this->saveJSONFile(
                        $componentsPath . DIRECTORY_SEPARATOR . $componentData["shortName"] . ".json",
                        $componentData
                    );
                }
            }
        }

        return $r;
    }
}