<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Enums;




/**
 * Tipo de elemento
 */
enum ElementType: string
{
    case UNKNOW = "UNKNOW";

    case CONSTANT = "CONSTANT";
    case VARIABLE = "VARIABLE";
    case PROPERTIE = "PROPERTIE";

    case FUNCTION = "FUNCTION";
    case METHOD = "METHOD";

    case INTERFACE = "INTERFACE";
    case ENUM = "ENUM";
    case TRAIT = "TRAIT";
    case CLASSE = "CLASSE";
}