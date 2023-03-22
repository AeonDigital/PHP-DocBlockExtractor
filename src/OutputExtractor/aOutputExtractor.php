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