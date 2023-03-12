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
abstract class DocumentationClassTest
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





    public int $pub_prop_01 = 10;
    public string $pub_prop_02 = "prop 02";

    protected int $prot_prop_01 = 11;
    private int $priv_prop_01 = 12;



    /**
     * Cria uma nova instância desta classe
     *
     * @throws Exception
     */
    function __construct()
    {
        //
    }



    /**
     * Tenta converter o tipo do valor passado para ``string``.
     * Apenas valores realmente compatíveis serão convertidos.
     *
     * Números de ponto flutuante serão convertidos e mantidos com no máximo 15 digitos
     * ao todo (parte inteira + parte decimal).
     * A parte decimal ficará com : (15 - (número de digitos da parte inteira)) casas.
     *
     * @param mixed $o
     * Objeto que será convertido.
     *
     * @return ?string
     * Retornará ``null`` caso não seja possível efetuar a conversão.
     */
    public function convertValueToString(mixed $o): ?string
    {
        return null;
    }
    /**
     * Trata ums string que representa um DocBlock para remover os caracteres que
     * servem unicamente para sua marcação e retorna um array onde cada entrada
     * representa uma das linhas de dados encontrado.
     *
     * @param string $rawDocBlock
     * String original do DocBlock que será analisado.
     *
     * @param int $another
     * Outro parametro para melhorar a cobertura dos testes.
     * Segundo descrição
     *
     * E finalizando.
     *
     * @return string[]
     */
    public static function parseRawDocBlockToRawLineArray(string $rawDocBlock = "..!", int $another = 55): array
    {
        return [];
    }
}