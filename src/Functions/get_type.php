<?php

declare(strict_types=1);






/**
 * Retorna o tipo scalar do valor passado.
 * Caso seja um objeto retornará o nome da classe que ele representa.
 *
 * @param mixed $o
 * Objeto que será verificado.
 *
 * @codeCoverageIgnore
 * Teste coberto no projeto ``PHP-Core`` na função ``Scalar::getType``.
 * Função foi portada para cá para tornar este projeto o mais independente possível.
 *
 * @return ?string
 */
function get_type(mixed $o): ?string
{
    $r = null;

    if ($o === null) {
        $r = "null";
    } elseif (\is_bool($o) === true) {
        $r = "bool";
    } elseif (\is_int($o) === true) {
        $r = "int";
    } elseif (\is_float($o) === true) {
        $r = "float";
    } elseif (\is_string($o) === true) {
        $r = "string";
    } elseif (\is_array($o) === true) {
        $r = "array";
    } elseif (\is_object($o) === true) {
        $r = \get_class($o);
    }

    return $r;
}