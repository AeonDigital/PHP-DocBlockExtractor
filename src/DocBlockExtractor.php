<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor;

use AeonDigital\DocBlockExtractor\Interfaces\iOutputExtractor as iOutputExtractor;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;
use AeonDigital\DocBlockExtractor\Enums\OutputFormatType as OutputFormatType;





/**
 * Processa e extrai o projeto indicado conforme as configurações passadas.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
class DocBlockExtractor
{



    /**
     * Cria um arquivo de configuração template no local indicado.
     *
     * @param string $configFilePath
     * Caminho completo até o arquivo de configuração.
     * Se um arquivo já existe ele será substituído.
     *
     *
     * @param string $rootPath
     * Valor usado na propriedade ``phpdocblockextractor[@rootPath]``.
     *
     * @param string $vendorDir
     * Valor usado na propriedade ``phpdocblockextractor[@vendorDir]``.
     *
     * @param string $outputDir
     * Valor usado na propriedade ``phpdocblockextractor[@outputDir]``.
     *
     * @param string|OutputFormatType $outputFormatType
     * Valor usado na propriedade ``phpdocblockextractor[@outputFormatType]``.
     *
     * @param bool $singleFile
     * Valor usado na propriedade ``phpdocblockextractor[@singleFile]``.
     *
     * @param string $outputExtractorClassName
     * Valor usado na propriedade ``phpdocblockextractor[@outputExtractorClassName]``.
     *
     * @param string[] $detachedFilesAndDirectories
     * Valores usados para popular o elemento ``phpdocblockextractor->detachedFilesAndDirectories``.
     *
     * @param string[] $ignoreDetachedFilesAndDirectories
     * Valores usados para popular o elemento ``phpdocblockextractor->ignoreDetachedFilesAndDirectories``.
     *
     *
     * @return bool
     * Retorna ``true`` caso a ação de criar o novo arquivo sucesso.
     */
    public static function createConfigurationFileTemplate(
        string $configFilePath,
        string $rootPath = "",
        string $vendorDir = "",
        string $outputDir = "",
        string|OutputFormatType $outputFormatType = "",
        bool $singleFile = true,
        string $outputExtractorClassName = "",
        array $detachedFilesAndDirectories = [],
        array $ignoreDetachedFilesAndDirectories = []
    ): bool {

        $r = file_put_contents(
            $configFilePath,
            file_get_contents(__DIR__ . "/XMLs/DocBlockExtractorConfigEmpty.xml")
        );
        $r = ($r !== false);


        if ($r === true) {
            if (\is_string($outputFormatType) === false) {
                $outputFormatType = $outputFormatType->value;
            }


            $configXML = \simplexml_load_file($configFilePath);
            $mainAttrs = $configXML->attributes();

            $mainAttrs->{"rootPath"} = $rootPath;
            $mainAttrs->{"vendorDir"} = $vendorDir;
            $mainAttrs->{"outputDir"} = $outputDir;
            $mainAttrs->{"outputFormatType"} = $outputFormatType;
            $mainAttrs->{"singleFile"} = (($singleFile === true) ? "true" : "false");
            $mainAttrs->{"outputExtractorClassName"} = $outputExtractorClassName;

            if (\count($detachedFilesAndDirectories) > 0) {
                $configXML->addChild("detachedFilesAndDirectories");
                foreach ($detachedFilesAndDirectories as $childPath) {
                    if (\is_file($childPath) === true) {
                        $configXML->detachedFilesAndDirectories->addChild("file", $childPath);
                    } elseif (\is_dir($childPath) === true) {
                        $configXML->detachedFilesAndDirectories->addChild("directory", $childPath);
                    }
                }
            }

            if (\count($ignoreDetachedFilesAndDirectories) > 0) {
                $configXML->addChild("ignoreDetachedFilesAndDirectories");
                foreach ($ignoreDetachedFilesAndDirectories as $childPath) {
                    if (\is_file($childPath) === true) {
                        $configXML->ignoreDetachedFilesAndDirectories->addChild("file", $childPath);
                    } elseif (\is_dir($childPath) === true) {
                        $configXML->ignoreDetachedFilesAndDirectories->addChild("directory", $childPath);
                    }
                }
            }



            // Formata XML antes de salvar.
            $strXML = $configXML->asXml();

            // Lista completa de configurações possíveis
            // http://tidy.sourceforge.net/docs/quickref.html
            $configOutput = [
                "input-xml"         => true,    // Indica que o código de entrada é um XML
                "output-xml"        => true,    // Tenta converter a string em um documento XML

                "indent"            => true,    // Indica se o código de saida deve estar identado
                "indent-spaces"     => 4,       // Indica a quantidade de espaços usados para cada nível de identação.
                "vertical-space"    => true,    // Irá adicionar algumas linhas em branco para facilitar a leitura.
                "wrap"              => 512,     // Máximo de caracteres que uma linha deve ter.

                "quote-ampersand"   => true,    // Converte todo & para &amp;
                "hide-comments"     => true,    // Remove comentários
                "indent-cdata"      => true,    // Identa sessões CDATA

                "char-encoding"     => "utf8"   // Encoding do código de saida.
            ];

            $tidy = \tidy_parse_string($strXML, $configOutput, "UTF8");
            $tidy->cleanRepair();
            $strXML = (string)$tidy;
            $r = \file_put_contents($configFilePath, $strXML);

            $r = ($r !== false);
        }


        return $r;
    }



    /**
     * Efetua a validação do arquivo de configuração para extração de documentação.
     *
     * @param string $configFilePath
     * Caminho completo até o arquivo de configuração.
     *
     * @param bool $returnError
     * Quando ``false`` retornará um booleano indicando o sucesso ou falha da validação.
     * Quando ``true`` retornará um array associativo contendo informações sobre a falha encontrada.
     * O array associativo usará a seguinte estrutura:
     * ```php
     * $arr = [
     *  "exceptionType" => ""       // Nome completo da exception que representa o tipo de erro encontrado.
     *  "exceptionMessage" => ""    // Mensagem de erro a ser incorporada na exception.
     * ];
     * ```
     *
     * @return bool|array
     * Retorna ``true`` ou ``false`` conforme o resultado da validação ou um array conforme informado
     * no parametro ``$returnError``.
     */
    public static function validateConfigXML(
        string $configFilePath,
        bool $returnError = false
    ): bool|array {
        $exceptionsNamespace = "AeonDigital\\DocBlockExtractor\\Exceptions\\";
        $configFilePath = \trim($configFilePath);

        $configXML = null;
        $exceptionType = "";
        $exceptionMessage = "";


        if ($configFilePath === "") {
            $exceptionType = "\\InvalidArgumentException";
            $exceptionMessage = "Path to configuration file cannot be empty.";
        } else {

            if (\is_file($configFilePath) === false) {
                $exceptionType = $exceptionsNamespace . "FileNotFoundException";
                $exceptionMessage = "Configuration file not found. [ $configFilePath ]";
            } else {
                $configXML = \simplexml_load_file($configFilePath);
                if ($configXML === false) {
                    $exceptionType = $exceptionsNamespace . "InvalidConfigurationFileException";
                    $exceptionMessage = "Cannot parse configuration file. [ $configFilePath ]";
                } else {
                    // Verifica os atributos definidos.
                    $mainAttrs = $configXML->attributes();
                    $minAttrs = [
                        "rootPath", "vendorDir", "outputDir", "outputFormatType", "singleFile", "outputExtractorClassName"
                    ];

                    $declaredAttrs = [];
                    foreach ($mainAttrs as $key => $value) {
                        $declaredAttrs[] = $key;
                    }

                    foreach ($minAttrs as $attrName) {
                        if (\in_array($attrName, $declaredAttrs) === false) {
                            $exceptionType = $exceptionsNamespace . "InvalidConfigurationFileException";
                            $exceptionMessage = "Invalid configuration path. Lost attribute \"$attrName\".";
                            break;
                        }
                    }


                    if ($exceptionType === "") {
                        $requiredInterface = "AeonDigital\\DocBlockExtractor\\Interfaces\\iOutputExtractor";

                        $outputExtractorClassName = (string)$mainAttrs->{"outputExtractorClassName"};
                        $classImplements = \class_implements($outputExtractorClassName);

                        if (
                            $outputExtractorClassName !== "" &&
                            (\is_array($classImplements) === false || \in_array($requiredInterface, $classImplements) === false)
                        ) {
                            $exceptionType = $exceptionsNamespace . "InvalidConfigurationFileException";
                            $exceptionMessage = "Output extractor class do not implements the required interface \"$requiredInterface\".";
                        }
                    }
                }
            }



            if ($configXML !== null && $exceptionType === "") {
                $rootPath = \rtrim((string)$mainAttrs->{"rootPath"}, "\\/");
                if ($rootPath === "") {
                    $rootPath = \dirname($configFilePath);
                }


                if (\realpath($rootPath) === false) {
                    $exceptionType = $exceptionsNamespace . "DirectoryNotFoundException";
                    $exceptionMessage = "Root path directory does not exists. [ $rootPath ]";
                } else {
                    $rootPath .= DIRECTORY_SEPARATOR;
                    $vendorDir = (string)$mainAttrs->{"vendorDir"};

                    if ($vendorDir === "" || \realpath($rootPath . $vendorDir) === false) {
                        $exceptionType = $exceptionsNamespace . "DirectoryNotFoundException";
                        $exceptionMessage = "Vendor directory does not exists. [ " . $rootPath . $vendorDir . " ]";
                    } else {
                        $outputDir = (string)$mainAttrs->{"outputDir"};

                        if ($outputDir === "" || \realpath($rootPath . $outputDir) === false) {
                            $exceptionType = $exceptionsNamespace . "DirectoryNotFoundException";
                            $exceptionMessage = "Output directory does not exists. [ " . $rootPath . $outputDir . " ]";
                        } else {
                            if (OutputFormatType::tryFrom((string)$mainAttrs->{"outputFormatType"}) === null) {
                                $exceptionType = $exceptionsNamespace . "InvalidConfigurationFileException";
                                $exceptionMessage = "Invalid output format type. [ " . (string)$mainAttrs->{"outputFormatType"} . " ]";
                            }
                        }
                    }
                }
            }
        }


        if ($returnError === false) {
            return ($exceptionType === "");
        } else {
            return [
                "exceptionType" => $exceptionType,
                "exceptionMessage" => $exceptionMessage
            ];
        }
    }



    /**
     * Retorna os diretórios e arquivos configurados em chaves <directory> e <file> inseridas dentro do
     * corpo do elemento passado em ``$xmlElement``.
     *
     * @param SimpleXMLElement $xmlElement
     * Elemento XML que será verificado em busca de chaves <directory> e <file>.
     *
     * @param string $rootPath
     * Caminho completo até a raiz do projeto ou local indicado para ser a base a partir da qual os
     * endereços de arquivos e diretórios estão descritos.
     *
     * @return array
     * Array contendo todos os endereços válidos de diretórios e arquivos descritos no elemento verificado.
     * Itens inválidos não causam erros, apenas não são usados.
     */
    public static function retrieveDirectoriesAndFilesFromXMLElement(
        \SimpleXMLElement $xmlElement,
        string $rootPath
    ): array {
        $r = [];
        if ($rootPath !== "") {
            $rootPath = rtrim($rootPath, "\\/") . DIRECTORY_SEPARATOR;
        }

        foreach ($xmlElement->directory as $child) {
            $rPath = \realpath($rootPath . (string)$child);
            if ($rPath !== false) {
                $r[] = $rPath;
            }
        }

        foreach ($xmlElement->file as $child) {
            $rPath = \realpath($rootPath . (string)$child);
            if ($rPath !== false) {
                $r[] = $rPath;
            }
        }

        $r = array_unique($r);
        return $r;
    }



    /**
     * Retorna uma instância ``iOutputExtractor`` referente ao extrator que será utilizado.
     *
     * @param string $outputExtractorClassName
     * Nome completo de uma classe concreta que implementa a interface
     * ``AeonDigital\DocBlockExtractor\Interfaces\iOutputExtractor``.
     */
    public static function retrieveOutputExtractorInstance(
        string $outputExtractorClassName
    ): iOutputExtractor {
        return new $outputExtractorClassName;
    }





    /**
     * Processa e extrai o projeto indicado conforme as configurações passadas.
     *
     * Para que a extração seja feita corretamente, primeiro você precisa rodar o
     * php composer para que as classes do projeto estejam devidamente mapeadas.
     *
     *
     * @param string $configPath
     * Caminho completo até o arquivo XML de configuração da extração de documentação.
     * Se usado, as configurações fornecidas por este arquivo serão as únicas usadas e
     * todos demais parametros passados serão ignorados.
     *
     *
     * @param string $rootPath
     * Quando definido será usado como prefixo de todos os demais parametros que apontam
     * para arquivos e diretórios.
     *
     * @param string $vendorDir
     * Caminho completo até o diretório ``vendor`` do projeto (ou caminho relativo iniciando
     * em ``$rootPath`` caso este esteja definido).
     *
     * @param string $outputDir
     * Caminho completo até um diretório que será usado como repositório dos arquivos
     * criados (ou caminho relativo iniciando em ``$rootPath`` caso este esteja definido).
     * O conteúdo original deste diretório será eliminado antes de gerar a nova documentação.
     *
     * @param string|OutputFormatType $outputFormatType
     * Tipo de formato de saída da documentação.
     * Se nenhum formato for informado, será usado ``JSON``.
     *
     * @param bool $singleFile
     * Esta opção é ``true`` por padrão.
     * Alguns tipos de formato (como JSON e XML) permitem que todo o conteúdo seja extraído
     * para um único arquivo. Informe ``true`` quando desejar este tipo de resultado. Em formatos
     * onde esta opção não faça sentido (como RST) esta opção será ignorada.
     *
     * @param string $outputExtractorClassName
     * Se usado deve indicar uma classe que implemente a interface ``iOutputExtractor`` que será
     * utilizada para obter o resultado final da extração.
     * O uso deste parametro fará ignorar a indicação informada em ``$outputFormatType``.
     *
     * @param string[] $detachedFilesAndDirectories
     * Array que, se usado, deve conter apenas caminhos para diretórios e arquivos que
     * não fazem parte do mapeamento feito pelo composer.
     *
     * Use caminhos completos caso ``$rootPath`` não seja definido e caminhos relativos iniciando
     * em ``$rootPath`` caso este esteja definido.
     *
     * @param string[] $ignoreDetachedFilesAndDirectories
     * Array que, se usado, deve conter apenas caminhos para diretórios e arquivos que
     * devem ser ignorados na extração da documentação.
     * Esta opção atinge apenas os itens ``detached``.
     *
     * Use caminhos completos caso ``$rootPath`` não seja definido e caminhos relativos iniciando
     * em ``$rootPath`` caso este esteja definido.
     *
     *
     * @throws DirectoryNotFoundException
     * Caso o diretório ``vendor``, ou o diretório indicado em ``$outputDir`` não exista.
     *
     * @throws FileNotFoundException
     * Caso algum dos arquivos mapeadores do composer sejam encontrados.
     * Caso o arquivo de configuração passado não seja encontrado.
     *
     * @throws InvalidConfigurationFile
     * Caso o arquivo de configuração não possa ser lido ou seja considerado inválido
     *
     * @throws InvalidConfigurationFileException
     * Caso alguma configuração do arquivo indicado esteja incorreta.
     *
     *
     *
     * @return bool
     * Indicando o sucesso ou não da extração da documentação.
     */
    public static function main(
        string $configPath = "",
        string $rootPath = "",
        string $vendorDir = "",
        string $outputDir = "",
        string|OutputFormatType $outputFormatType = "",
        bool $singleFile = true,
        string $outputExtractorClassName = "",
        array $detachedFilesAndDirectories = [],
        array $ignoreDetachedFilesAndDirectories = []
    ): bool {

        if ($configPath === "") {
            $configPath = \sys_get_temp_dir() . "/docBlockExtractorConfigFile.xml";
            self::createConfigurationFileTemplate(
                $configPath,
                $rootPath,
                $vendorDir,
                $outputDir,
                $outputFormatType,
                $singleFile,
                $outputExtractorClassName,
                $detachedFilesAndDirectories,
                $ignoreDetachedFilesAndDirectories
            );
        }



        $r = self::validateConfigXML($configPath, true);
        if ($r["exceptionType"] !== "") {
            $exceptionType = $r["exceptionType"];
            throw new $exceptionType($r["exceptionMessage"]);
        } else {
            $configXML = \simplexml_load_file($configPath);
            $mainAttrs = $configXML->attributes();

            $rootPath = (string)$mainAttrs->{"rootPath"};
            if ($rootPath !== "") {
                $rootPath = \rtrim($rootPath, "\\/") . DIRECTORY_SEPARATOR;
            }
            $vendorDir = \realpath($rootPath . (string)$mainAttrs->{"vendorDir"});
            $outputDir = \realpath($rootPath . (string)$mainAttrs->{"outputDir"});

            $outputFormatType = OutputFormatType::from(
                \strtoupper((string)$mainAttrs->{"outputFormatType"})
            );

            $singleFile = \strtolower((string)$mainAttrs->{"singleFile"});
            $singleFile = ($singleFile === "1" || $singleFile === "true");

            $outputExtractorClassName = (string)$mainAttrs->{"outputExtractorClassName"};
            if ($outputExtractorClassName === "") {
                $outputExtractorClassName = "AeonDigital\\DocBlockExtractor\\OutputExtractor\\" . $outputFormatType->value;
            }


            $detachedFilesAndDirectories = self::retrieveDirectoriesAndFilesFromXMLElement(
                $configXML->detachedFilesAndDirectories,
                $rootPath
            );
            $ignoreDetachedFilesAndDirectories = self::retrieveDirectoriesAndFilesFromXMLElement(
                $configXML->ignoreDetachedFilesAndDirectories,
                $rootPath
            );


            $extractor = self::retrieveOutputExtractorInstance(
                $outputExtractorClassName
            );
            return $extractor->extract(
                new ProjectDocumentation(
                    $vendorDir,
                    $detachedFilesAndDirectories,
                    $ignoreDetachedFilesAndDirectories,
                ),
                $outputDir,
                $singleFile
            );
        }
    }
}
