<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\OutputExtractor;

use AeonDigital\DocBlockExtractor\OutputExtractor\aOutputExtractor as aOutputExtractor;
use AeonDigital\DocBlockExtractor\Exceptions\DirectoryNotFoundException as DirectoryNotFoundException;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;





/**
 * Efetua a extração dos dados de uma classe ``ProjectDocumentation`` em
 * um ou mais arquivos XML.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
class XML extends aOutputExtractor
{


    protected \SimpleXMLElement $objXml;
    protected array $mapComponentCollectionTypeToObjectName = [
        "constants" => "constant",
        "variables" => "variable",
        "functions" => "function",

        "interfaces" => "interface",
        "enuns" => "enun",
        "traits" => "trait",
        "classes" => "class",
    ];
    /*protected array $mapComponentChildCollectionTypeToObjectName = [
        "constants" => [
            "public" => "constant",
        ],
        "properties" => [
            "public" => [
                "static" => "property",
                "nonstatic" => "property",
            ],
        ],
        "methods" => [
            "public" => [
                "abstract" => [
                    "static" => "method",
                    "nonstatic" => "method",
                ],
                "nonabstract" => [
                    "static" => "method",
                    "nonstatic" => "method",
                ]
            ],
        ],
    ];*/



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
     * Quando ``true`` o conteúdo será extraido para um único arquivo chamado ``index.xml``.
     * Se ``false``, cada namespace representará um diretório dentro de ``$outputDir`` e
     * dentro de cada um serão alocados os seguintes arquivos/diretórios:
     *
     * - constants.xml      [ 1 arquivo para todas as constantes da namespace ]
     * - variables.xml      [ 1 arquivo para todas as variáveis da namespace ]
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

        $this->objXml = new \SimpleXMLElement("<documentation></documentation>");

        foreach ($structuredDocumentation as $namespaceName => $namespaceComponentsCollections) {
            $this->appendNamespace(
                $namespaceName,
                $namespaceComponentsCollections
            );
        }

        if ($singleFile === true) {
            $r = $this->objXml->saveXML($outputDir . DIRECTORY_SEPARATOR . "index.xml");
        }

        return $r;
    }





    protected function appendNamespace(
        string $namespaceName,
        array $namespaceComponentsCollections
    ): void {
        $objNS = $this->objXml->addChild("namespace");
        $objNS->addAttribute("name", $namespaceName);

        foreach ($namespaceComponentsCollections as $componentCollectionType => $componentCollectionDataObjects) {
            $objXMLComponentCollection = $objNS->addChild($componentCollectionType);

            foreach ($componentCollectionDataObjects as $objCollectionDataObject) {
                $objXMLComponent = $objXMLComponentCollection->addChild(
                    $this->mapComponentCollectionTypeToObjectName[$componentCollectionType]
                );

                $this->appendComponent(
                    $objXMLComponent,
                    $objCollectionDataObject
                );
            }
        }
    }



    protected function appendComponent(
        \SimpleXMLElement $objXMLComponent,
        array $objCollectionDataObject
    ): void {

        foreach ($objCollectionDataObject as $paramAttributeName => $paramAttributeValue) {
            switch ($paramAttributeName) {
                case "fileName":
                case "namespaceName":
                case "fqsen":
                case "shortName":
                case "type":
                    $objXMLComponent->addAttribute($paramAttributeName, $paramAttributeValue);
                    break;

                case "docBlock":
                    $objXMLElem = $objXMLComponent->addChild("docBlock");
                    $this->appendDocBlock($objXMLElem, $paramAttributeValue);
                    break;

                case "interfaces":
                case "extends":
                    $objXMLElem = $objXMLComponent->addChild($paramAttributeName);
                    if (\is_array($paramAttributeValue) && \count($paramAttributeValue) > 0) {
                        foreach ($paramAttributeValue as $value) {
                            $objXMLElem->addChild("ns", $value);
                        }
                    }
                    break;

                case "isAbstract":
                case "isFinal":
                    if (\is_bool($paramAttributeValue) === true) {
                        $paramAttributeValue = (($paramAttributeValue === true) ? "true" : "false");
                    }
                    $objXMLComponent->addAttribute($paramAttributeName, $paramAttributeValue);
                    break;

                case "constants":
                    $objXMLElem = $objXMLComponent->addChild($paramAttributeName);
                    // Seguir daqui!!!!
                    $objXMLPublicElem = $objXMLElem->addChild("public");

                    break;
            }
            /*
            if (\key_exists($paramAttributeName, $this->mapComponentCollectionTypeToObjectName) === true) {
                $objXMLChildCollection = $objXMLComponent->addChild(
                    $this->mapComponentCollectionTypeToObjectName[$paramAttributeName]
                );

                if (\is_array($paramAttributeValue) === true && \count($paramAttributeValue) > 0) {
                    $this->appendComponent(
                        $objXMLChildCollection,
                        $objCollectionDataObject
                    );
                }
            } else {
                if ($paramAttributeName === "docBlock") {
                    $objXMLDocBlock = $objXMLComponent->addChild("docBlock");
                    $this->appendDocBlock($objXMLDocBlock, $paramAttributeValue);
                } else {
                    if (\is_bool($paramAttributeValue) === true) {
                        $paramAttributeValue = (($paramAttributeValue === true) ? "true" : "false");
                    } elseif ($paramAttributeValue === null) {
                        $paramAttributeValue = "null";
                    }

                    $objXMLComponent->addAttribute($paramAttributeName, $paramAttributeValue);
                }
            }*/
        }

        /*
        $objXMLDocBlock = $objXMLComponent->addChild("docBlock");
        $this->appendDocBlock($objXMLDocBlock, $objCollectionDataObject["docBlock"]);


        switch ($objCollectionDataObject["type"]) {
            case "CONSTANT":
            case "VARIABLE":
            case "PROPERTIE":
                // resgatar valores!
                break;

            case "FUNCTION":
            case "METHOD":
                $objXMLParameters = $objXMLComponent->addChild("parameters");
                $this->appendParameters(
                    $objXMLParameters,
                    $objCollectionDataObject["parameters"]
                );
                $objXMLComponent->addChild("return", $objCollectionDataObject["return"]);
                break;

            case "INTERFACE":
            case "ENUM":
            case "TRAIT":
            case "CLASSE":

                break;
        }
        */
    }



    protected function appendDocBlock(
        \SimpleXMLElement $objXMLDocBlock,
        array $objDocBlock
    ): void {
        $objSummary = $objXMLDocBlock->addChild("summary");
        foreach ($objDocBlock["summary"] as $line) {
            $objSummary->addChild("l", (string)$line);
        }

        $objDescription = $objXMLDocBlock->addChild("description");
        foreach ($objDocBlock["description"] as $line) {
            $objDescription->addChild("l", (string)$line);
        }

        $objTags = $objXMLDocBlock->addChild("tags");
        foreach ($objDocBlock["tags"] as $tagName => $tagDescription) {
            $objTag = $objTags->addChild("tag");
            $objTag->addAttribute("name", $tagName);

            foreach ($tagDescription as $line) {
                $objTag->addChild("l", (string)$line);
            }
        }
    }


    protected function appendParameters(
        \SimpleXMLElement $objXMLParameters,
        array $objDataParameters
    ): void {
        foreach ($objDataParameters as $parameterName => $objParameterAttributess) {
            $objXMLParameter = $objXMLParameters->addChild("parameter");
            $objXMLParameter->addAttribute("name", $parameterName);

            foreach ($objParameterAttributess as $paramAttributeName => $paramAttributeValue) {
                if ($paramAttributeName === "docBlock") {
                    $objXMLDocBlock = $objXMLParameter->addChild("docBlock");
                    $this->appendDocBlock($objXMLDocBlock, $paramAttributeValue);
                } else {
                    $objXMLParameter->addAttribute($paramAttributeName, (string)$paramAttributeValue);
                }
            }
        }
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

        foreach ($data as $namespaceName => $namespaceComponents) {
        }
        return (($r === false) ? false : true);
    }
}