<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor\Functions;

use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;
use AeonDigital\DocBlockExtractor\DataObject as DataObject;

/**
 * Processa a lista de mapeamento de classes criada pelo composer para extrair
 * apenas aqueles itens que pertencem ao projeto que está sendo usado no momento.
 *
 * @param string $vendorDir
 * Caminho completo até o diretório ``vendor`` do projeto atual.
 *
 * @return array
 * Array associativo contendo os itens pertencentes à este projeto.
 * Nas chaves virá o FQN de cada objeto e associada a ela estará um objeto
 * do tipo ``DataObject``.
 */
function retrieveProjectDataMap(string $vendorDir): array
{
    $r = [];
    $classMapData = include $vendorDir . "/composer/autoload_classmap.php";

    // Remove da lista os itens que vem de outros projetos.
    foreach ($classMapData as $fqn => $filePath) {
        if (\str_starts_with($filePath, $vendorDir) === true) {
            unset($classMapData[$fqn]);
        } else {
            $r[$fqn] = new DataObject(
                $filePath,
                $fqn,
                ElementType::UNKNOW
            );
        }
    }

    return $r;
}