<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Interfaces;

use AeonDigital\DocBlockExtractor\ProjectDocumentation as ProjectDocumentation;







/**
 * Interface para classes concretas capazes de performar a extração da documentação para
 * um determinado formato.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
interface iOutputExtractor
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
    ): bool;
}