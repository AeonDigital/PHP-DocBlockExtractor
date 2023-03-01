<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Tests;









/**
 * Classe fake para teste de extração de documentação.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
class DocumentationClassTest
{
    /** @var int PUB_CONST_01 Uma constante de teste. */
    public const PUB_CONST_01 = 1;
    /**
     * Outra constante de teste.
     *
     * Descrição mais abaixo.
     *
     * @var
     */
    public const PUB_CONST_02 = "1";
    protected const PROT_CONST_01 = "not shows";
    private const PRIV_CONST_01 = "not shows";
}