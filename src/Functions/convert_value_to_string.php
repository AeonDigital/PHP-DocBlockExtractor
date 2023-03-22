<?php

declare(strict_types=1);






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
 * @codeCoverageIgnore
 * Teste coberto no projeto ``PHP-Core`` na função ``Convert::toString``.
 * Função foi portada para cá para tornar este projeto o mais independente possível.
 *
 * @return ?string
 * Retornará ``null`` caso não seja possível efetuar a conversão.
 */
function convert_value_to_string(mixed $o): ?string
{
    if ($o === null) {
        return "";
    } elseif (\is_bool($o) === true) {
        return (($o === true) ? "1" : "0");
    } elseif (\is_int($o) === true) {
        return (string)$o;
    } elseif (\is_float($o) === true) {
        $int = \numeric_integer_part($o);
        $dec = 0.0;

        $tDec = (15 - \strlen((string)$int));
        if ($tDec > 0) {
            $dec = \numeric_decimal_part($o, $tDec);
        }

        if ($dec === 0.0) {
            return ((string)$int);
        } else {
            $dec = \str_replace("0.", "", (string)$dec);
            return ((string)($int . "." . $dec));
        }
    } elseif (\is_a($o, "\DateTime") === true) {
        return $o->format("Y-m-d H:i:s");
    } elseif (\is_string($o) === true) {
        return (string)$o;
    } elseif (\is_array($o) === true) {
        return \implode(" ", $o);
    } else {
        return null;
    }
}