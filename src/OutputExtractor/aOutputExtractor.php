<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\OutputExtractor;

use AeonDigital\DocBlockExtractor\Interfaces\iOutputExtractor as iOutputExtractor;
use AeonDigital\DocBlockExtractor\Exceptions\DirectoryNotFoundException as DirectoryNotFoundException;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;





/**
 * Classe abstrata usada pelas classes concretas que performam a extração da documentação
 * para formatos específicos.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
abstract class aOutputExtractor implements iOutputExtractor
{



    /**
     * Prepara para a extração dos dados na classe concreta.
     *
     * Executa verificações que independem do tipo de formato.
     * Exclui totalmente o conteúdo do diretório ``$outputDir``.
     * Retorna um array associativo contendo todos os componentes da documentação agrupados
     * em suas respectivas namespaces.
     *
     * @param ProjectDocumentation $proDoc
     * Instância a partir da qual a documentação será obtida.
     *
     * @param string $outputDir
     * Caminho completo até um diretório que será usado como repositório dos arquivos
     * criados.
     *
     * @throws DirectoryNotFoundException
     * Caso o diretório ``outputDir`` indicado não exista.
     *
     * @throws RuntimeException
     * Caso não seja possível excluir totalmente o conteúdo do diretório ``$outputDir``.
     *
     * @codeCoverageIgnore
     *
     * @return array
     * Array associativo com as informações da documentação.
     */
    protected function prepareExtraction(
        ProjectDocumentation $proDoc,
        string $outputDir
    ): array {
        if (\is_dir($outputDir) === false) {
            throw new DirectoryNotFoundException("Directory not found. [ $outputDir ]");
        }
        if ($this->dir_deltree($outputDir, false) === false) {
            throw new \RuntimeException("Could not delete contents of output directory [ $outputDir ].");
        }

        $components = [
            "constants" => $proDoc->getConstants(),
            "variables" => $proDoc->getVariables(),
            "functions" => $proDoc->getFunctions(),
            "interfaces" => $proDoc->getInterfaces(),
            "enuns" => $proDoc->getEnuns(),
            "traits" => $proDoc->getTraits(),
            "classes" => $proDoc->getClasses(),
        ];

        $structuredDocumentation = [];
        foreach ($proDoc->getNamespaces() as $namespace) {
            $structuredDocumentation[$namespace] = [];

            foreach ($components as $componentType => $componentObjects) {
                if (
                    \key_exists($namespace, $componentObjects) === true &&
                    $componentObjects[$namespace] !== []
                ) {
                    $structuredDocumentation[$namespace][$componentType] = $componentObjects[$namespace];
                }
            }
        }

        return $structuredDocumentation;
    }





    /**
     * Efetua a extração da documentação para formatos cuja saída não exige tratamento especial
     * além de individualizar os arquivos de componentes como XML e JSON.
     *
     * @param ProjectDocumentation $proDoc
     * Instância a partir da qual a documentação será obtida.
     *
     * @param string $outputDir
     * Caminho completo até um diretório que será usado como repositório dos arquivos
     * criados. O conteúdo original deste diretório será eliminado antes de gerar a nova
     * documentação.
     *
     * @param string $outputFormat
     * Indica o formato em que os dados devem ser salvos.
     * Eperado: json | xml
     *
     * @param bool $singleFile
     * Quando ``true`` o conteúdo será extraido para um único arquivo chamado ``index.xyz``.
     * Se ``false``, cada namespace representará um diretório dentro de ``$outputDir`` e
     * dentro de cada um serão alocados os seguintes arquivos/diretórios:
     *
     * - constants.xyz      [ 1 arquivo para todas as constantes da namespace ]
     * - variables.xyz      [ 1 arquivo para todas as variáveis da namespace ]
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
    public function genericExtract(
        ProjectDocumentation $proDoc,
        string $outputDir,
        string $outputFormat,
        bool $singleFile
    ): bool {
        $r = false;

        $structuredDocumentation = $this->prepareExtraction(
            $proDoc,
            $outputDir
        );

        if ($singleFile === true) {
            $r = $this->saveDocumentFile(
                $outputDir . DIRECTORY_SEPARATOR . "index." . $outputFormat,
                $outputFormat,
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
                                        $r = $this->saveDocumentFile(
                                            $namespaceNamePath . DIRECTORY_SEPARATOR . $componentType . "." . $outputFormat,
                                            $outputFormat,
                                            $componentObjects
                                        );
                                        break;

                                    case "functions":
                                    case "interfaces":
                                    case "enuns":
                                    case "traits":
                                    case "classes":
                                        $r = $this->saveDocumentsOfComponentsFiles(
                                            $namespaceNamePath . DIRECTORY_SEPARATOR . $componentType,
                                            $outputFormat,
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
     * Salva os dados passados para um arquivo no formato indicado.
     *
     * @param string $absolutePathToFile
     * Caminho completo até o local onde o novo arquivo será criado.
     *
     * @param string $outputFormat
     * Indica o formato em que os dados devem ser salvos.
     * Eperado: json | xml
     *
     * @param array $data
     * Informações que serão salvas no arquivo.
     *
     * @return bool
     * Retorna ``true`` se o arquivo for salvo corretamente.
     */
    protected function saveDocumentFile(
        string $absolutePathToFile,
        string $outputFormat,
        array $data,
    ): bool {
        $r = false;

        if ($outputFormat === "json") {
            $jsonData = \json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            $r = \file_put_contents($absolutePathToFile, $jsonData);
            $r = (($r === false) ? false : true);
        }

        return $r;
    }
    /**
     * Salva os dados passados para arquivos individualizados sendo 1 para cada
     * componente listado. Os arquivos terão o mesmo nome indicado na chave ``shortName`` de
     * suas respectivas descrições.
     *
     * @param string $componentsPath
     * Caminho completo até o diretório onde os novos arquivos serão gerados.
     *
     * @param string $outputFormat
     * Indica o formato em que os dados devem ser salvos.
     * Eperado: json | xml
     *
     * @param array $componentObjects
     * Array contendo cada um dos componentes que deve ser individualizado em um arquivo a parte.
     *
     * @return bool
     * Retorna ``true`` se todos os arquivos forem salvos corretamente.
     */
    protected function saveDocumentsOfComponentsFiles(
        string $componentsPath,
        string $outputFormat,
        array $componentObjects
    ): bool {
        $r = \mkdir($componentsPath);

        if ($r === true) {
            foreach ($componentObjects as $componentData) {
                if ($r === true) {
                    $r = $this->saveDocumentFile(
                        $componentsPath . DIRECTORY_SEPARATOR . $componentData["shortName"] . "." . $outputFormat,
                        $outputFormat,
                        $componentData
                    );
                }
            }
        }

        return $r;
    }





    /**
     * Remove um diretório e todo seu conteúdo.
     *
     * @param string $absoluteSystemPathToDir
     * Diretório que será excluido.
     *
     * @param bool $removeMain
     * Quando ``true`` irá remover tudo, incluindo o próprio diretório indicado no
     * argumento ``$absoluteSystemPathToDir``. Se ``false``, irá limpar totalmente conteúdo do
     * diretório indicado e mantê-lo.
     *
     * @codeCoverageIgnore
     * Teste coberto no projeto ``PHP-Core`` na função ``dir_deltree``.
     * Função foi portada para cá para tornar este projeto o mais independente possível.
     *
     * @return bool
     * Retornará ``true`` se o diretório alvo for excluído.
     */
    private function dir_deltree(string $absoluteSystemPathToDir, bool $removeMain = true): bool
    {
        if (\is_dir($absoluteSystemPathToDir) === true) {
            $r = true;
            $allObjects = \array_diff(\scandir($absoluteSystemPathToDir), [".", ".."]);

            foreach ($allObjects as $object) {
                if ($r === true) {
                    $path = $absoluteSystemPathToDir . DIRECTORY_SEPARATOR . $object;
                    if (\is_dir($path) === true) {
                        $r = $this->dir_deltree($path, true);
                    } else {
                        $r = \unlink($path);
                    }
                }
            }

            if ($r === true && $removeMain === true) {
                $r = \rmdir($absoluteSystemPathToDir);
            }

            return $r;
        }
        return false;
    }
}