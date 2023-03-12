<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor;

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
     * @return bool
     * Retorna ``true`` caso a ação de criar o novo arquivo sucesso.
     */
    public static function createConfigurationFileTemplate(string $configFilePath): bool
    {
        $r = file_put_contents(
            $configFilePath,
            file_get_contents(__DIR__ . "/DocBlockExtractorConfigTemplate.xml")
        );
        return ($r === false) ? false : true;
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
    public static function validateConfigXML(string $configFilePath, bool $returnError = false): bool|array
    {
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
                        $requiredInterface = "AeonDigital\DocBlockExtractor\Interfaces\iOutputExtractor";
                        $outputExtractorClassName = (string)$mainAttrs->{"outputExtractorClassName"};
                        if (
                            $outputExtractorClassName !== "" &&
                            \in_array($requiredInterface, \class_implements($outputExtractorClassName)) === false
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
        $rootPath = rtrim($rootPath, "\\/") . DIRECTORY_SEPARATOR;

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
     * Processa e extrai o projeto indicado conforme as configurações passadas.
     *
     * Para que a extração seja feita corretamente, primeiro você precisa rodar o
     * php composer para que as classes do projeto estejam devidamente mapeadas.
     *
     * @param string $configPath
     * Caminho completo até o arquivo XML de configuração da extração de documentação.
     * Se usado, as configurações fornecidas por este arquivo serão as únicas usadas e
     * todos demais parametros passados serão ignorados.
     *
     * @param string $vendorDir
     * Caminho completo até o diretório ``vendor`` do projeto.
     *
     * @param string[] $detachedFilesAndDirectories
     * Array que, se usado, deve conter apenas caminhos para diretórios e arquivos que
     * não fazem parte do mapeamento feito pelo composer.
     *
     * @param string[] $ignoreDetachedFilesAndDirectories
     * Array que, se usado, deve conter apenas caminhos para diretórios e arquivos que
     * devem ser ignorados na extração da documentação.
     * Esta opção atinge apenas os itens ``detached``.
     *
     * @param string $outputDir
     * Caminho completo até um diretório que será usado como repositório dos arquivos
     * criados. O conteúdo original deste diretório será eliminado antes de gerar a nova
     * documentação.
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
     */
    public static function main(
        string $configPath = "",
        string $vendorDir = "",
        array $detachedFilesAndDirectories = [],
        array $ignoreDetachedFilesAndDirectories = [],
        string $outputDir = "",
        string|OutputFormatType $outputFormatType = "",
        bool $singleFile = true,
        string $outputExtractorClassName = ""
    ): void {

        if ($configPath !== "") {
            $r = self::validateConfigXML($configPath, true);
            if ($r["exceptionType"] !== "") {
                $exceptionType = $r["exceptionType"];
                throw new $exceptionType($r["exceptionMessage"]);
            }


            $configXML = \simplexml_load_file($configPath);
            $mainAttrs = $configXML->attributes();


            $rootPath = \rtrim((string)$mainAttrs->{"rootPath"}, "\\/");
            if ($rootPath === "") {
                $rootPath = \dirname($configPath);
            }
            $rootPath .= DIRECTORY_SEPARATOR;


            $vendorDir = \realpath($rootPath . (string)$mainAttrs->{"vendorDir"});
            $detachedFilesAndDirectories = self::retrieveDirectoriesAndFilesFromXMLElement(
                $configXML->detachedFilesAndDirectories,
                $rootPath
            );
            $ignoreDetachedFilesAndDirectories = self::retrieveDirectoriesAndFilesFromXMLElement(
                $configXML->ignoreDetachedFilesAndDirectories,
                $rootPath
            );
            $outputDir = \realpath($rootPath . (string)$mainAttrs->{"outputDir"});
            $outputFormatType = OutputFormatType::from(\strtoupper((string)$mainAttrs->{"outputFormatType"}));
            $singleFile = \strtolower((string)$mainAttrs->{"singleFile"});
            if ($singleFile === "1" || $singleFile === "true") {
                $singleFile = true;
            } else {
                $singleFile = false;
            }
        }


        // Inicia um objeto ``ProjectDocumentation`` e efetua o parse lógico das informações do projeto
        /*$proDoc = new ProjectDocumentation(
            $vendorDir,
            $detachedFilesAndDirectories,
            $ignoreDetachedFilesAndDirectories,
        );*/
    }
}