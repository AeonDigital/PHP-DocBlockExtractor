<?php

declare(strict_types=1);

namespace AeonDigital\SimpleTypes\Enums;









/**
 * Enumerador dos tipos primitivos que podem ser representados por
 * classes ``SimpleTypes`` concretas.
 *
 * @package     AeonDigital\SimpleTypes
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
enum PrimitiveType: string
{
    /**
     * Valor booleano.
     */
    case BOOL = "bool";
    /**
     * Numeral inteiro de 8 bits.
     */
    case BYTE = "byte";
    /**
     * Numeral inteiro de 16 bits.
     */
    case SHORT = "short";
    /**
     * Numeral inteiro de 32 bits.
     */
    case INT = "int";
    /**
     * Numeral inteiro de 64 bits.
     */
    case LONG = "long";
    /**
     * Numeral de ponto flutuante de 32 bits.
     */
    case FLOAT = "float";
    /**
     * Numeral de ponto flutuante de 64 bits.
     */
    case DOUBLE = "double";
    /**
     * Numeral Real.
     */
    case REAL = "\AeonDigital\RealType";
    /**
     * Objeto DateTime.
     */
    case DATETIME = "\DateTime";
    /**
     * Valor string.
     */
    case STRING = "string";
}
