<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Enums;




/**
 * Tipo de formato de saída de processamento de documentação
 */
enum OutputFormatType: string
{
    case JSON = "JSON";
    case XML = "XML";
    case RST = "RST";
}