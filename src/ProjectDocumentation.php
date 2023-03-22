<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor;

use AeonDigital\DocBlockExtractor\Exceptions\DirectoryNotFoundException as DirectoryNotFoundException;
use AeonDigital\DocBlockExtractor\Exceptions\FileNotFoundException as FileNotFoundException;
use AeonDigital\DocBlockExtractor\ObjectDocumentation as ObjectDocumentation;
use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;




/**
 * Responsável por processar obter os objetos que compõe o projeto que está sendo analisado.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
class ProjectDocumentation
{



    /** @var string[] $fileNames */
    private array $fileNames = [];
    /**
     * Retorna um array unidimensional contendo todos arquivos usados para
     * extrair a informação da documentação do projeto.
     *
     * @return string[]
     */
    public function getFileNames(): array
    {
        return $this->fileNames;
    }



    /** @var string[] $namespaces */
    private array $namespaces = [];
    /**
     * Retorna um array unidimensional contendo todas as namespaces
     * declaradas no projeto.
     *
     * @return string[]
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }



    /** @var array[string]ObjectDocumentation[] $constants */
    private array $constants;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e as constantes.
     *
     * @return array[string]ObjectDocumentation[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /** @var array[string]ObjectDocumentation[] $variables */
    private array $variables;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e as variáveis.
     *
     * @return array[string]ObjectDocumentation[]
     */
    public function getVariables(): array
    {
        return $this->variables;
    }



    /** @var array[string]ObjectDocumentation[] $functions */
    private array $functions;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e as funções.
     *
     * @return array[string]ObjectDocumentation[]
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }



    /** @var array[string]ObjectDocumentation[] $interfaces */
    private array $interfaces;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e suas interfaces.
     *
     * @return array[string]ObjectDocumentation[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /** @var array[string]ObjectDocumentation[] $enums */
    private array $enums;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e seus enuns.
     *
     * @return array[string]ObjectDocumentation[]
     */
    public function getEnuns(): array
    {
        return $this->enums;
    }

    /** @var array[string]ObjectDocumentation[] $traits */
    private array $traits;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e suas traits.
     *
     * @return array[string]ObjectDocumentation[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    /** @var array[string]ObjectDocumentation[] $classes */
    private array $classes;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e suas classes.
     *
     * @return array[string]ObjectDocumentation[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }










    /**
     * Efetua a extração da documentação do projeto.
     *
     * Para que a extração seja feita corretamente, primeiro você precisa rodar o
     * php composer para que as classes do projeto estejam devidamente mapeadas.
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
     * @throws DirectoryNotFoundException
     * Caso o diretório ``vendor`` indicado não exista.
     *
     * @throws FileNotFoundException
     * Caso algum dos arquivos mapeadores do composer sejam encontrados.
     */
    function __construct(
        string $vendorDir,
        array $detachedFilesAndDirectories = [],
        array $ignoreDetachedFilesAndDirectories = []
    ) {
        if (\is_dir($vendorDir) === false) {
            throw new DirectoryNotFoundException("Directory not found. [ $vendorDir ]");
        }

        $pathToClassMap = $vendorDir . "/composer/autoload_classmap.php";
        if (\is_file($pathToClassMap) === false) {
            throw new FileNotFoundException("File not found. [ $pathToClassMap ]");
        }

        $project = $this->scanProjectFiles(
            $vendorDir,
            $detachedFilesAndDirectories,
            $ignoreDetachedFilesAndDirectories
        );


        $projectObjects = [];
        if (\is_array($project["projectObjects"]) === true) {
            foreach ($project["projectObjects"] as $fqsen => $fileName) {
                $projectObjects[] = (new ObjectDocumentation(
                    $fileName,
                    $fqsen,
                    ElementType::UNKNOW
                ))->toArray();
            }
        }

        if (\is_array($project["detachedObjects"]) === true) {
            foreach ($project["detachedObjects"] as $fileName) {
                $projectObjects[] = (new ObjectDocumentation(
                    $fileName,
                    "",
                    ElementType::UNKNOW
                ))->toArray();
            }
        }


        $this->classifyProjectObjects($projectObjects);
    }










    /**
     * Escaneia os diretórios e arquivos indicados para retornar um array associativo que contenha apenas
     * os itens que devem mesmo serem usados para resgatar a documentação do projeto.
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
     * @return array
     * Retorna um array associativo conforme o seguinte modelo:
     * ```php
     * $arr = [
     *  "projectObjects" => [],
     *  "detachedObjects" => []
     * ];
     * ```
     *
     * A chave ``projectObjects`` trará um array associativo contendo o nome de cada objeto encontrado
     * correlacionado com o respectivo arquivo onde elo está definido.
     * Já a chave ``detachedObjects`` traz um array unidimensional contendo apenas os nomes de arquivos
     * avulsos que fazem parte do projeto mas que não fazem parte do mapeamento padrão do composer.
     */
    protected function scanProjectFiles(
        string $vendorDir,
        array $detachedFilesAndDirectories = [],
        array $ignoreDetachedFilesAndDirectories = []
    ): array {


        // Inicia a coleta dos objetos do projeto a partir daqueles que estão mapeados pelo composer.
        // Neste momento interfaces, enumeradores, traits, e classes serão coletadas e registradas.
        // Serão removidos da lista os itens que vem de outros projetos.
        $projectObjects = include $vendorDir . "/composer/autoload_classmap.php";
        foreach ($projectObjects as $fqsen => $filePath) {
            if (\str_starts_with($filePath, $vendorDir) === true) {
                unset($projectObjects[$fqsen]);
            }
        }


        //
        // Identifica os arquivos avulsos que devem ser carregados com o projeto e a partir deles
        // identifica variáveis, constantes e funções que estão dispersas e por algum motivo não
        // foram mapeadas.
        $detachedFiles = [];
        if (\count($detachedFilesAndDirectories) > 0) {
            $pathToFilesMap = $vendorDir . "/composer/autoload_files.php";

            if (\is_file($pathToFilesMap) === true) {
                $detachedFiles = \array_values(include $pathToFilesMap);


                // Remove da lista de arquivos avulsos todos os itens que vem de outros projetos.
                for ($i = (\count($detachedFiles) - 1); $i >= 0; $i--) {
                    if (\str_starts_with($detachedFiles[$i], $vendorDir) === true) {
                        unset($detachedFiles[$i]);
                    }
                }
                $detachedFiles = \array_values($detachedFiles);


                // Adiciona a lista de arquivos que são carregados dinamicamente.
                foreach ($detachedFilesAndDirectories as $targetPath) {
                    if (\is_file($targetPath) === true) {
                        $detachedFiles[] = $targetPath;
                    } elseif (\is_dir($targetPath) === true) {
                        array_push($detachedFiles, ...(\dir_scan_w_r($targetPath)));
                    }
                }
                $detachedFiles = \array_values($detachedFiles);


                // Remove todos arquivos que não são de extensão .php
                for ($i = (\count($detachedFiles) - 1); $i >= 0; $i--) {
                    if (\str_ends_with($detachedFiles[$i], ".php") === false) {
                        unset($detachedFiles[$i]);
                    }
                }
                $detachedFiles = \array_values($detachedFiles);


                // Remove da lista os itens indicados para serem ignorados.
                $ignoreDirs = [];
                $ignoreFiles = [];
                foreach ($ignoreDetachedFilesAndDirectories as $targetIgnore) {
                    if (\is_dir($targetIgnore) === true) {
                        $ignoreDirs[] = $targetIgnore;
                    } elseif (\is_file($targetIgnore) === true) {
                        $ignoreFiles[] = $targetIgnore;
                    }
                }


                foreach ($ignoreDirs as $ignoreDir) {
                    for ($i = (\count($detachedFiles) - 1); $i >= 0; $i--) {
                        if (\str_starts_with($detachedFiles[$i], $ignoreDir . "\\")) {
                            unset($detachedFiles[$i]);
                        }
                    }
                }
                $detachedFiles = \array_values($detachedFiles);

                foreach ($ignoreFiles as $ignoreFile) {
                    for ($i = (\count($detachedFiles) - 1); $i >= 0; $i--) {
                        if ($ignoreFile === $detachedFiles[$i]) {
                            unset($detachedFiles[$i]);
                        }
                    }
                }
                $detachedFiles = \array_values($detachedFiles);
            }
        }


        return [
            "projectObjects" => $projectObjects,
            "detachedObjects" => $detachedFiles
        ];
    }
    /**
     * Classifica os objetos do projeto conforme seus tipos identificados.
     *
     * @param array $projectObjects
     * Coleção de objetos que devem ser classificados nesta instância.
     *
     * @return void
     */
    protected function classifyProjectObjects(array $projectObjects): void
    {
        foreach ($projectObjects as $objectDocumentation) {
            $fn = $objectDocumentation["fileName"];
            $ns = $objectDocumentation["namespaceName"];
            $tp = $objectDocumentation["type"];


            if (\in_array($fn, $this->fileNames) === false) {
                $this->fileNames[] = $fn;
            }


            if (\in_array($ns, $this->namespaces) === false) {
                $this->namespaces[] = $ns;

                $this->constants[$ns] = [];
                $this->variables[$ns] = [];
                $this->functions[$ns] = [];

                $this->interfaces[$ns] = [];
                $this->enums[$ns] = [];
                $this->traits[$ns] = [];
                $this->classes[$ns] = [];
            }


            switch ($tp) {
                case "CONSTANT";
                    $this->constants[$ns][] = $objectDocumentation;
                    break;
                case "VARIABLE";
                    $this->variables[$ns][] = $objectDocumentation;
                    break;
                case "FUNCTION";
                    $this->functions[$ns][] = $objectDocumentation;
                    break;


                case "INTERFACE";
                    $this->interfaces[$ns][] = $objectDocumentation;
                    //$this->classifyProjectObjects([$objectDocumentation]);
                    break;
                case "ENUM";
                    $this->enums[$ns][] = $objectDocumentation;
                    //$this->classifyProjectObjects($objectDocumentation);
                    break;
                case "TRAIT";
                    $this->traits[$ns][] = $objectDocumentation;
                    //$this->classifyProjectObjects($objectDocumentation);
                    break;
                case "CLASSE";
                    $this->classes[$ns][] = $objectDocumentation;
                    //$this->classifyProjectObjects([$objectDocumentation]);
                    break;
            }
        }
    }
}