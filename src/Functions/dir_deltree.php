<?php

declare(strict_types=1);






/**
 * Remove um diretório e todo seu conteúdo.
 *
 * @param string $absoluteSystemPathToDir
 * Diretório que será excluido.
 *
 * @param bool $removeMain
 * Quando ``true`` irá remover tudo, incluindo o próprio diretório indicado no
 * argumento ``$absoluteSystemPathToDir``. Se ``false``, irá limpar totalmente conteúdo do
 * diretório indicado e mantê-lo.
 *
 * @codeCoverageIgnore
 * Teste coberto no projeto ``PHP-Core`` na função ``dir_deltree``.
 * Função foi portada para cá para tornar este projeto o mais independente possível.
 *
 * @return bool
 * Retornará ``true`` se o diretório alvo for excluído.
 */
function dir_deltree(string $absoluteSystemPathToDir, bool $removeMain = true): bool
{
    if (\is_dir($absoluteSystemPathToDir) === true) {
        $r = true;
        $allObjects = \array_diff(\scandir($absoluteSystemPathToDir), [".", ".."]);

        foreach ($allObjects as $object) {
            if ($r === true) {
                $path = $absoluteSystemPathToDir . DIRECTORY_SEPARATOR . $object;
                if (\is_dir($path) === true) {
                    $r = \dir_deltree($path, true);
                } else {
                    $r = \unlink($path);
                }
            }
        }

        if ($r === true && $removeMain === true) {
            $r = \rmdir($absoluteSystemPathToDir);
        }

        return $r;
    }
    return false;
}