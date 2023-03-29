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
            $r = $this->saveDocumentFile(
                $outputDir . DIRECTORY_SEPARATOR . "index.xml",
                $this->objXml->asXml()
            );
        } else {
            $r = true;

            foreach ($this->objXml->namespace as $objXMLNamespace) {
                if ($r === true) {
                    $objXMLAttributes = $objXMLNamespace->attributes();
                    $namespaceNamePath = $outputDir . DIRECTORY_SEPARATOR . \str_replace("\\", "_", (string)$objXMLAttributes->{"name"});

                    $r = \mkdir($namespaceNamePath);
                    if ($r === true) {

                        foreach ($objXMLNamespace as $objNamespaceComponent) {
                            if ($r === true && $objNamespaceComponent->count() > 0) {
                                $componentType = $objNamespaceComponent->getName();

                                switch ($componentType) {
                                    case "constants":
                                    case "variables":
                                        $r = $this->saveDocumentFile(
                                            $namespaceNamePath . DIRECTORY_SEPARATOR . $componentType . ".xml",
                                            $objNamespaceComponent->asXML()
                                        );
                                        break;

                                    case "functions":
                                    case "interfaces":
                                    case "enuns":
                                    case "traits":
                                    case "classes":
                                        $r = $this->saveDocumentsOfComponentsFiles(
                                            $namespaceNamePath . DIRECTORY_SEPARATOR . $componentType,
                                            $objNamespaceComponent
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
     * Adiciona um novo node ``namespace`` no objeto ``$this->objXml``.
     *
     * @param string $namespaceName
     * Nome completo do namespace.
     *
     * @param array $namespaceComponentsCollections
     * Coleção de componentes que devem ser adicionados na namespace.
     *
     * @return void
     */
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



    /**
     * Adiciona um novo componente no nó pai indicado.
     *
     * @param \SimpleXMLElement $objXMLParentNode
     * Elemento XML alvo das alterações.
     *
     * @param array $objCollectionDataObject
     * Coleção de objetos que devem ser adicionados no elemento XML.
     *
     * @return void
     */
    protected function appendComponent(
        \SimpleXMLElement $objXMLParentNode,
        array $objCollectionDataObject
    ): void {

        foreach ($objCollectionDataObject as $paramAttributeName => $paramAttributeValue) {
            switch ($paramAttributeName) {
                case "fileName":
                case "namespaceName":
                case "fqsen":
                case "shortName":
                case "type":

                case "isAbstract":
                case "isFinal":
                    $objXMLParentNode->addAttribute(
                        $paramAttributeName,
                        $this->convertJSONValueToXMLValue($paramAttributeValue)
                    );
                    break;

                case "docBlock":
                    $objXMLElem = $objXMLParentNode->addChild("docBlock");
                    $this->appendDocBlock($objXMLElem, $paramAttributeValue);
                    break;

                case "interfaces":
                case "extends":
                    $objXMLElem = $objXMLParentNode->addChild($paramAttributeName);
                    if (\is_array($paramAttributeValue) && \count($paramAttributeValue) > 0) {
                        foreach ($paramAttributeValue as $value) {
                            $objXMLElem->addChild("ns", $value);
                        }
                    }
                    break;

                case "constants":
                    $objXMLElem = $objXMLParentNode->addChild($paramAttributeName);
                    $objXMLPublicElem = $objXMLElem->addChild("public");

                    $this->appendChildCollection(
                        $objXMLPublicElem,
                        "constant",
                        $paramAttributeValue["public"]
                    );
                    break;

                case "properties":
                    $objXMLElem = $objXMLParentNode->addChild($paramAttributeName);
                    $objXMLPublicElem = $objXMLElem->addChild("public");

                    $this->appendChildCollection(
                        $objXMLPublicElem->addChild("static"),
                        "property",
                        $paramAttributeValue["public"]["static"]
                    );

                    $this->appendChildCollection(
                        $objXMLPublicElem->addChild("nonstatic"),
                        "property",
                        $paramAttributeValue["public"]["nonstatic"]
                    );
                    break;

                case "value":
                case "defaultValue":
                    $objXMLElem = $objXMLParentNode->addChild($paramAttributeName);

                    $objXMLElem->addAttribute("type", $this->convertJSONValueToXMLValue($paramAttributeValue["type"]));
                    $objXMLElem->addAttribute("originalValue", $this->convertJSONValueToXMLValue($paramAttributeValue["originalValue"]));
                    $objXMLElem->addAttribute("stringValue", $this->convertJSONValueToXMLValue($paramAttributeValue["stringValue"]));
                    break;

                case "constructor":
                    $objXMLElem = $objXMLParentNode->addChild($paramAttributeName);

                    if ($paramAttributeValue !== null) {
                        $this->appendComponent(
                            $objXMLElem,
                            $paramAttributeValue
                        );
                    }
                    break;

                case "methods":
                    $objXMLElem = $objXMLParentNode->addChild($paramAttributeName);
                    $objXMLPublicElem = $objXMLElem->addChild("public");


                    $objXMLPublicAbstractElem = $objXMLPublicElem->addChild("abstract");
                    $this->appendChildCollection(
                        $objXMLPublicAbstractElem->addChild("static"),
                        "method",
                        $paramAttributeValue["public"]["abstract"]["static"]
                    );
                    $this->appendChildCollection(
                        $objXMLPublicAbstractElem->addChild("nonstatic"),
                        "method",
                        $paramAttributeValue["public"]["abstract"]["nonstatic"]
                    );


                    $objXMLPublicNonAbstractElem = $objXMLPublicElem->addChild("nonabstract");
                    $this->appendChildCollection(
                        $objXMLPublicNonAbstractElem->addChild("static"),
                        "method",
                        $paramAttributeValue["public"]["nonabstract"]["static"]
                    );
                    $this->appendChildCollection(
                        $objXMLPublicNonAbstractElem->addChild("nonstatic"),
                        "method",
                        $paramAttributeValue["public"]["nonabstract"]["nonstatic"]
                    );

                    break;

                case "parameters":
                    $objXMLElem = $objXMLParentNode->addChild($paramAttributeName);
                    $this->appendParameters($objXMLElem, $paramAttributeValue);

                    break;

                case "return":
                    $objXMLElem = $objXMLParentNode->addChild(
                        $paramAttributeName,
                        $this->convertJSONValueToXMLValue($paramAttributeValue)
                    );
                    break;
            }
        }
    }



    /**
     * Preenche um nó ``DocBlock`` com os dados passados.
     *
     * @param \SimpleXMLElement $objXMLParentNode
     * Elemento XML alvo das alterações.
     *
     * @param array $objDocBlock
     * Dados do elemento ``DocBlock``.
     *
     * @return void
     */
    protected function appendDocBlock(
        \SimpleXMLElement $objXMLParentNode,
        array $objDocBlock
    ): void {
        $objSummary = $objXMLParentNode->addChild("summary");
        foreach ($objDocBlock["summary"] as $line) {
            $objSummary->addChild("line", (string)$line);
        }

        $objDescription = $objXMLParentNode->addChild("description");
        foreach ($objDocBlock["description"] as $line) {
            $objDescription->addChild("line", (string)$line);
        }

        $objTags = $objXMLParentNode->addChild("tags");
        foreach ($objDocBlock["tags"] as $tagName => $objTagData) {

            if (\is_array($objTagData) === false || \count($objTagData) === 0) {
                $objTag = $objTags->addChild("tag");
                $objTag->addAttribute("name", $tagName);
            } else {
                if (\is_string($objTagData[0]) === true) {
                    $objTag = $objTags->addChild("tag");
                    $objTag->addAttribute("name", $tagName);

                    foreach ($objTagData as $line) {
                        $objTag->addChild("line", (string)$line);
                    }
                } elseif (\is_array($objTagData[0]) === true) {
                    foreach ($objTagData as $oTag) {
                        $objTag = $objTags->addChild("tag");
                        $objTag->addAttribute("name", $tagName);

                        foreach ($oTag as $line) {
                            $objTag->addChild("line", (string)$line);
                        }
                    }
                }
            }
        }
    }



    /**
     * Preenche o nó pai com uma série de elementos filhos.
     *
     * @param \SimpleXMLElement $objXMLParentNode
     * Elemento XML alvo das alterações.
     *
     * @param string $tagNameElement
     * TagName para os filhos que serão criados.
     *
     * @param array $objChildElements
     * Array de arrays contendo a coleção de dados de objetos filhos a
     * serem adicionados.
     *
     * @return void
     */
    protected function appendChildCollection(
        \SimpleXMLElement $objXMLParentNode,
        string $tagNameElement,
        array $objChildElements
    ): void {
        if (\is_array($objChildElements) && \count($objChildElements) > 0) {
            foreach ($objChildElements as $objChileElem) {
                $this->appendComponent(
                    $objXMLParentNode->addChild($tagNameElement),
                    $objChileElem
                );
            }
        }
    }



    /**
     * Preenche um nó ``parameters`` com os parametros que fazem parte de sua
     * especificação.
     *
     * @param \SimpleXMLElement $objXMLParentNode
     * Elemento XML alvo das alterações.
     *
     * @param array $objDataParameters
     * Array associativo de arrays contendo a coleção de dados de objetos filhos a
     * serem adicionados.
     *
     * @return void
     */
    protected function appendParameters(
        \SimpleXMLElement $objXMLParentNode,
        array $objDataParameters
    ): void {
        foreach ($objDataParameters as $parameterName => $objParameterAttributess) {
            $objXMLParameter = $objXMLParentNode->addChild("parameter");
            $objXMLParameter->addAttribute("name", $parameterName);

            foreach ($objParameterAttributess as $paramAttributeName => $paramAttributeValue) {
                if ($paramAttributeName === "docBlock") {
                    $objXMLDocBlock = $objXMLParameter->addChild("docBlock");
                    $this->appendDocBlock($objXMLDocBlock, $paramAttributeValue);
                } else {
                    $paramAttributeValue = $this->convertJSONValueToXMLValue($paramAttributeValue);
                    $objXMLParameter->addAttribute($paramAttributeName, $paramAttributeValue);
                }
            }
        }
    }



    /**
     * Converte o valor JSON a ser usado em uma tag XML para um valor em ``string``
     * que lhe represente neste formato.
     *
     * @param mixed $o
     * Valor que será convertido.
     *
     * @return string
     */
    protected function convertJSONValueToXMLValue(mixed $o): string
    {
        if ($o === null) {
            $o = "``null``";
        } elseif (\is_bool($o) === true) {
            $o = (($o === true) ? "true" : "false");
        } elseif (\is_array($o) === true) {
            $o = "```array``";
        } else {
            $o = (string)$o;
        }

        return $o;
    }





    /**
     * Salva os dados passados para um arquivo no formato indicado.
     *
     * @param string $absolutePathToFile
     * Caminho completo até o local onde o novo arquivo será criado.
     *
     * @param string $strXML
     * Informações que serão salvas no arquivo.
     *
     * @return bool
     * Retorna ``true`` se o arquivo for salvo corretamente.
     */
    protected function saveDocumentFile(
        string $absolutePathToFile,
        string $strXML,
    ): bool {
        // Lista completa de configurações possíveis
        // http://tidy.sourceforge.net/docs/quickref.html
        $configOutput = [
            "input-xml"         => true,    // Indica que o código de entrada é um XML
            "output-xml"        => true,    // Tenta converter a string em um documento XML

            "add-xml-decl"      => true,    // Adiciona a declaração de documento XML no início do arquivo.

            "indent"            => true,    // Indica se o código de saida deve estar indentado
            "indent-spaces"     => 4,       // Indica a quantidade de espaços usados para cada nível de indentação.
            "indent-attributes" => true,    // Indenta os atributos
            "vertical-space"    => true,    // Irá adicionar algumas linhas em branco para facilitar a leitura.
            "wrap"              => 512,     // Máximo de caracteres que uma linha deve ter.

            "quote-ampersand"   => true,    // Converte todo & para &amp;
            "hide-comments"     => true,    // Remove comentários
            "indent-cdata"      => true,    // Indenta sessões CDATA

            "char-encoding"     => "utf8"   // Encoding do código de saida.
        ];

        $tidy = \tidy_parse_string($strXML, $configOutput, "UTF8");
        $tidy->cleanRepair();
        $strXML = (string)$tidy;
        $r = \file_put_contents($absolutePathToFile, $strXML);

        return ($r !== false);
    }
    /**
     * Salva os dados passados para arquivos individualizados sendo 1 para cada
     * componente listado. Os arquivos terão o mesmo nome indicado em seu ``shortName`` de
     * suas respectivas descrições.
     *
     * @param string $absolutePathToDir
     * Caminho completo até o diretório onde os novos arquivos serão gerados.
     *
     * @param \SimpleXMLElement $componentObjects
     * Array contendo cada um dos componentes que deve ser individualizado em um arquivo a parte.
     *
     * @return bool
     * Retorna ``true`` se todos os arquivos forem salvos corretamente.
     */
    protected function saveDocumentsOfComponentsFiles(
        string $absolutePathToDir,
        \SimpleXMLElement $componentObjects
    ): bool {
        $r = \mkdir($absolutePathToDir);

        if ($r === true) {
            foreach ($componentObjects as $componentData) {
                if ($r === true) {
                    $objXMLAttributes = $componentData->attributes();

                    $r = $this->saveDocumentFile(
                        $absolutePathToDir . DIRECTORY_SEPARATOR . $objXMLAttributes->{"shortName"} . ".xml",
                        $componentData->asXML()
                    );
                }
            }
        }

        return $r;
    }
}
