<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\OutputExtractor;

use AeonDigital\DocBlockExtractor\OutputExtractor\aOutputExtractor as aOutputExtractor;
use AeonDigital\DocBlockExtractor\Exceptions\DirectoryNotFoundException as DirectoryNotFoundException;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;





/**
 * Efetua a extração dos dados de uma classe ``ProjectDocumentation`` em
 * uma coleção de arquivos ``RST`` prevendo compatibilidade com o serviço ``ReadTheDocs``.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
class RST extends aOutputExtractor
{


    protected array $mapComponentCollectionTypeToObjectName = [
        "constants"     => "constant",
        "variables"     => "variable",
        "functions"     => "function",

        "interfaces"    => "interface",
        "enuns"         => "enun",
        "traits"        => "trait",
        "classes"       => "class",
    ];



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
     * O formato ``RST`` não é compatível com esta opção.
     * O resultado do processamento será sempre extraído para uma série de arquivos compatíveis
     * com o que é esperado para o consumo do serviço ``ReadTheDocs``.
     *
     * - constants.rst      [ 1 arquivo para todas as constantes da namespace ]
     * - variables.rst      [ 1 arquivo para todas as variáveis da namespace ]
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
        $r = true;

        $structuredDocumentation = $this->prepareExtraction(
            $proDoc,
            $outputDir
        );


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
                                    $r = $this->saveDocumentFile(
                                        $namespaceNamePath . DIRECTORY_SEPARATOR . $componentType . ".rst",
                                        $componentObjects
                                    );
                                    break;
                                    /*
                                case "functions":
                                case "interfaces":
                                case "enuns":
                                case "traits":
                                case "classes":
                                    $r = $this->saveDocumentsOfComponentsFiles(
                                        $namespaceNamePath . DIRECTORY_SEPARATOR . $componentType,
                                        $componentObjects
                                    );
                                    break;*/
                            }
                        }
                    }
                }
            }
        }

        return $r;
    }





    protected function retrieveConstantRSTHeader(): string
    {
        $str = [];
        $str[] = "=========";
        $str[] = "Constants";
        $str[] = "=========";

        return \implode("\n", $str);
    }
    protected function retrieveConstantRSTElements(array $componentData): string
    {
        $str[] = ".. php:const:: " . $componentData["shortName"];
        $str[] = "";

        if (
            \key_exists("docBlock", $componentData) === true &&
            \is_array($componentData["docBlock"]) === true &&
            \key_exists("summary", $componentData["docBlock"]) === true &&
            \is_array($componentData["docBlock"]["summary"]) === true &&
            \key_exists("description", $componentData["docBlock"]) === true &&
            \is_array($componentData["docBlock"]["description"]) === true
        ) {
            $ind = "    ";

            if (\count($componentData["docBlock"]["summary"]) > 0) {
                foreach ($componentData["docBlock"]["summary"] as $line) {
                    if ($line === "") {
                        $str[] = "";
                    } else {
                        $str[] = $ind . "| " . $line;
                    }
                }

                if (\count($componentData["docBlock"]["description"]) > 0) {
                    $str[] = "";
                }
            }

            if (\count($componentData["docBlock"]["description"]) > 0) {
                foreach ($componentData["docBlock"]["description"] as $line) {
                    if ($line === "") {
                        $str[] = "";
                    } else {
                        $str[] = $ind . "| " . $line;
                    }
                }
            }
        }

        return \implode("\n", $str);
    }



    protected function retrieveVariableRSTHeader(): string
    {
        $str = [];
        $str[] = "=========";
        $str[] = "Variables";
        $str[] = "=========";

        return \implode("\n", $str);
    }
    protected function retrieveVariableRSTElements(array $componentData): string
    {
        $str[] = ".. php:var:: " . $componentData["shortName"];
        $str[] = "";

        if (
            \key_exists("docBlock", $componentData) === true &&
            \is_array($componentData["docBlock"]) === true &&
            \key_exists("summary", $componentData["docBlock"]) === true &&
            \is_array($componentData["docBlock"]["summary"]) === true &&
            \key_exists("description", $componentData["docBlock"]) === true &&
            \is_array($componentData["docBlock"]["description"]) === true
        ) {
            $ind = "    ";

            if (\count($componentData["docBlock"]["summary"]) > 0) {
                foreach ($componentData["docBlock"]["summary"] as $line) {
                    if ($line === "") {
                        $str[] = "";
                    } else {
                        $str[] = $ind . "| " . $line;
                    }
                }

                if (\count($componentData["docBlock"]["description"]) > 0) {
                    $str[] = "";
                }
            }

            if (\count($componentData["docBlock"]["description"]) > 0) {
                foreach ($componentData["docBlock"]["description"] as $line) {
                    if ($line === "") {
                        $str[] = "";
                    } else {
                        $str[] = $ind . "| " . $line;
                    }
                }
            }
        }

        return \implode("\n", $str);
    }





    /**
     * Salva os dados passados para um arquivo no formato indicado.
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
    protected function saveDocumentFile(
        string $absolutePathToFile,
        array $data,
    ): bool {

        $strDocument = "";
        $componentType = "";
        if (\count($data) > 0) {
            $componentType = $data[0]["type"];

            switch ($componentType) {
                case "CONSTANT":
                    $strHeader = $this->retrieveConstantRSTHeader();
                    $strBody = [];

                    foreach ($data as $componentData) {
                        $strBody[] = $this->retrieveConstantRSTElements($componentData);
                    }

                    $strDocument = $strHeader . "\n\n\n" . \implode("\n\n\n\n", $strBody);
                    break;

                case "VARIABLE":
                    $strHeader = $this->retrieveVariableRSTHeader();
                    $strBody = [];

                    foreach ($data as $componentData) {
                        $strBody[] = $this->retrieveVariableRSTElements($componentData);
                    }

                    $strDocument = $strHeader . "\n\n\n" . \implode("\n\n\n\n", $strBody);
                    break;
            }
        }

        $r = \file_put_contents($absolutePathToFile, $strDocument);
        return (($r === false) ? false : true);
    }
    /**
     * Salva os dados passados para arquivos individualizados sendo 1 para cada
     * componente listado. Os arquivos terão o mesmo nome indicado em seu ``shortName`` de
     * suas respectivas descrições.
     *
     * @param string $absolutePathToDir
     * Caminho completo até o diretório onde os novos arquivos serão gerados.
     *
     * @param array $componentObjects
     * Array contendo cada um dos componentes que deve ser individualizado em um arquivo a parte.
     *
     * @return bool
     * Retorna ``true`` se todos os arquivos forem salvos corretamente.
     */
    protected function saveDocumentsOfComponentsFiles(
        string $absolutePathToDir,
        array $componentObjects
    ): bool {
        $r = \mkdir($absolutePathToDir);

        if ($r === true) {
            foreach ($componentObjects as $componentData) {
                if ($r === true) {
                    $r = $this->saveDocumentFile(
                        $absolutePathToDir . DIRECTORY_SEPARATOR . $componentData["shortName"] . ".rst",
                        $componentData
                    );
                }
            }
        }

        return $r;
    }
}