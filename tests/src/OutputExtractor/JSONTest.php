<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AeonDigital\DocBlockExtractor\OutputExtractor\JSON as JSON;
use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;

require_once __DIR__ . "/../../phpunit.php";





class JSONTests extends TestCase
{


    private string $rootPath = "";
    private string $vendorPath = "";
    private string $pathToTestClasses = "";
    private string $pathToTestJSONs = "";
    private string $pathToTestXMLs = "";
    private string $pathToOutputDocs = "";

    private function setTestDirs(): void
    {
        if ($this->rootPath === "") {
            $this->rootPath = realpath(__DIR__ . "/../../../");
            $this->vendorPath = $this->rootPath . "/vendor";
            $this->pathToTestClasses = $this->rootPath . "/tests/resources/testClasses";
            $this->pathToTestJSONs = $this->rootPath . "/tests/resources/testJSONs";
            $this->pathToTestXMLs = $this->rootPath . "/tests/resources/testXMLs";
            $this->pathToOutputDocs = $this->rootPath . "/tests/resources/testOutputDocs";
        }
    }





    public function test_method_fails_extract()
    {
        $this->setTestDirs();

        $proDoc = new ProjectDocumentation($this->vendorPath);
        $obj = new JSON();

        $fail = false;
        try {
            $obj->extract(
                $proDoc,
                "invalid",
                true
            );
        } catch (\Exception $ex) {
            $fail = true;
            $this->assertSame("Directory not found. [ invalid ]", $ex->getMessage());
        }
        $this->assertTrue($fail, "Test must fail");
    }



    public function test_method_extract_toSingleFile()
    {
        $this->setTestDirs();

        $proDoc = new ProjectDocumentation($this->vendorPath);
        $obj = new JSON();

        $expectedDir = $this->pathToOutputDocs . "/JSONSingle/expected";
        $resultDir = $this->pathToOutputDocs . "/JSONSingle/result";

        $this->assertTrue(
            $obj->extract(
                $proDoc,
                $resultDir,
                true
            )
        );

        $expectedFiles = $this->dir_scan_w_r($expectedDir);
        $resultFiles = $this->dir_scan_w_r($resultDir);

        $this->assertTrue(\count($resultFiles) > 0);
        $this->assertEquals(\count($expectedFiles), \count($resultFiles));

        foreach ($resultFiles as $resultFilePath) {
            $expectedFilePath = \str_replace($resultDir, $expectedDir, $resultFilePath);
            $this->assertTrue(\is_file($expectedFilePath));

            $expectedContent = \file_get_contents($expectedFilePath);
            $resultContent = \file_get_contents($resultFilePath);

            $this->assertEquals($expectedContent, $resultContent);
        }
    }



    public function test_method_extract_toMultipleFiles()
    {
        $this->setTestDirs();

        $proDoc = new ProjectDocumentation($this->vendorPath);
        $obj = new JSON();

        $expectedDir = $this->pathToOutputDocs . "/JSONMultiple/expected";
        $resultDir = $this->pathToOutputDocs . "/JSONMultiple/result";

        $this->assertTrue(
            $obj->extract(
                $proDoc,
                $resultDir,
                false
            )
        );

        $expectedFiles = $this->dir_scan_w_r($expectedDir);
        $resultFiles = $this->dir_scan_w_r($resultDir);

        $this->assertTrue(\count($resultFiles) > 0);
        $this->assertEquals(\count($expectedFiles), \count($resultFiles));

        foreach ($resultFiles as $resultFilePath) {
            $expectedFilePath = \str_replace($resultDir, $expectedDir, $resultFilePath);
            $this->assertTrue(\is_file($expectedFilePath));

            $expectedContent = \file_get_contents($expectedFilePath);
            $resultContent = \file_get_contents($resultFilePath);

            $this->assertEquals($expectedContent, $resultContent);
        }
    }











    /**
     * Retorna a listagem do conteúdo do diretório alvo já ordenado adequadamente conforme o
     * padrão Windows.
     *
     * @param string $absoluteSystemPathToDir
     * Diretório que será listado.
     *
     * @codeCoverageIgnore
     * Teste coberto no projeto ``PHP-Core`` na função ``dir_scan_w``.
     * Função foi portada para cá para tornar este projeto o mais independente possível.
     *
     * @return array
     * Lista de diretórios e arquivos encontrados no local indicado.
     */
    protected function dir_scan_w(string $absoluteSystemPathToDir): array
    {
        $dirContent = \scandir($absoluteSystemPathToDir);

        $tgtDirs = [];
        $tgtDotFiles = [];
        $tgtUnderFiles = [];
        $tgtFiles = [];

        foreach ($dirContent as $tgtName) {
            if (
                $tgtName === "." ||
                $tgtName === ".." ||
                \is_dir($absoluteSystemPathToDir . DIRECTORY_SEPARATOR . $tgtName) === true
            ) {
                $tgtDirs[] = $tgtName;
            } else {
                if ($tgtName[0] === ".") {
                    $tgtDotFiles[] = $tgtName;
                } else {
                    if ($tgtName[0] === "_") {
                        $tgtUnderFiles[] = $tgtName;
                    } else {
                        $tgtFiles[] = $tgtName;
                    }
                }
            }
        }

        // Reordena os itens e refaz o index dos elementos.
        \natcasesort($tgtDirs);
        \natcasesort($tgtDotFiles);
        \natcasesort($tgtUnderFiles);
        \natcasesort($tgtFiles);

        $dirContent = \array_merge($tgtDirs, $tgtDotFiles, $tgtUnderFiles, $tgtFiles);

        return $dirContent;
    }
    /**
     * Retorna um array contendo o caminho completo para todos os arquivos dentro do diretório
     * alvo. Esta ação é recursiva.
     *
     * @param string $absoluteSystemPathToDir
     * Diretório que será listado.
     *
     * @codeCoverageIgnore
     * Teste coberto no projeto ``PHP-Core`` na função ``dir_scan_w_r``.
     * Função foi portada para cá para tornar este projeto o mais independente possível.
     *
     * @return string[]
     * Lista de arquivos encontrados no local indicado.
     */
    protected function dir_scan_w_r(string $absoluteSystemPathToDir): array
    {
        $r = [];
        $dirContent = $this->dir_scan_w($absoluteSystemPathToDir);
        foreach ($dirContent as $tgtName) {
            if ($tgtName !== "." && $tgtName !== "..") {
                $fullPath = $absoluteSystemPathToDir . DIRECTORY_SEPARATOR . $tgtName;
                if (\is_dir($fullPath) === true) {
                    $r = \array_merge($r, $this->dir_scan_w_r($fullPath));
                } else {
                    $r[] = $fullPath;
                }
            }
        }
        return $r;
    }
}