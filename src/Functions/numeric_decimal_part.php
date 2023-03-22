<?php

declare(strict_types=1);






/**
 * Retorna unicamente a parte decimal de um numeral.
 *
 * Por questões internas referentes a forma como os numerais de ponto flutuantes funcionam, a
 * maior precisão possível de ser encontrada é a de números de até 15 dígitos, independente do
 * local onde está o ponto decimal.
 *
 * @param int|float $n
 * Valor numérico de entrada.
 *
 * @param int $l
 * Tamanho da parte decimal a ser retornada.
 * Se não for informado, será usado o valor **2**.
 *
 * @codeCoverageIgnore
 * Teste coberto no projeto ``PHP-Core`` na função ``numeric_decimal_part``.
 * Função foi portada para cá para tornar este projeto o mais independente possível.
 *
 * @return float
 * Retornará um ``float`` como ``0.004321``.
 */
function numeric_decimal_part(int|float $n, int $l = 2): float
{
    $r = 0.0;

    if (\is_float($n) === true) {
        $str = \explode(".", \number_format($n, $l, ".", ""));
        $str = "0." . $str[1];
        $r = (float)$str;
    }

    return $r;
}