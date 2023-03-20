<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\OutputExtractor;

use AeonDigital\DocBlockExtractor\Interfaces\iOutputExtractor as iOutputExtractor;
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
class JSON implements iOutputExtractor
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
     * Quando ``true`` o conteúdo será extraido para um único arquivo.
     * Esta opção só deve ser aceita caso faça sentido para o formato implementado, caso contrário
     * deve ser ignorado.
     */
    public function extract(
        ProjectDocumentation $proDoc,
        string $outputDir,
        bool $singleFile
    ): bool {
        return false;
    }
}