<?php

declare(strict_types=1);

namespace AeonDigital\Standalone;



/**
 * Constante de teste
 *
 * @var array CStandalone
 */
const CStandalone = ["array"];


/** @var string $VStandalone Variável de teste */
$VStandalone = "test";


/**
 * Verifica se as chaves definidas como obrigatórias de um ``Array Associativo`` estão realmente
 * presentes.
 *
 * @param array $keys
 * Coleção com o nome das chaves obrigatórias.
 *
 * @param array $array
 * ``Array associativo`` que será verificado.
 *
 * @return array
 * Retorna um ``array`` contendo o nome de cada um dos itens que **NÃO** foram definidos.
 * Ou seja, se retornar um ``array`` vazio, significa que todas as chaves foram definidas.
 */
function array_check_required_keys(array $keys, array $array): array
{
    $arrReturn = [];

    foreach ($keys as $k) {
        if (isset($array[$k]) === false) {
            $arrReturn[] = $k;
        }
    }

    return $arrReturn;
}