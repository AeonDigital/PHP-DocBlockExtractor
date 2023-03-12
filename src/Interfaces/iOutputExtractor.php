<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Interfaces;









/**
 * Interface para as classes de extratores de documentação para determinado formato.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
interface iOutputExtractor
{



    /**
     * Tipo primitivo representado por este campo.
     *
     * @var iSimpleType
     */
    public function extractDocumentation(): bool;
}