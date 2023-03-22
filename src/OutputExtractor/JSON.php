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
        return $this->genericExtract(
            $proDoc,
            $outputDir,
            "json",
            $singleFile
        );
    }
}