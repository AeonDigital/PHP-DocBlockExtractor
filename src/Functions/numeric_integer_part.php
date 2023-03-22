<?php

declare(strict_types=1);






/**
 * Retorna unicamente a parte inteira de um numeral.
 *
 * @param int|float $n
 * Valor numérico de entrada.
 *
 * @codeCoverageIgnore
 * Teste coberto no projeto ``PHP-Core`` na função ``numeric_integer_part``.
 * Função foi portada para cá para tornar este projeto o mais independente possível.
 *
 * @return int
 * Retorna a parte inteira do numeral passado.
 */
function numeric_integer_part(int|float $n): int
{
    $r = $n;

    if (\is_float($n) === true) {
        $str = \number_format($n, 10, ".", "");
        $str = \explode(".", $str);
        $r = (int)$str[0];
    }

    return $r;
}