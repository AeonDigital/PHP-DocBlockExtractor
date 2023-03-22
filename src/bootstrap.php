<?php

declare(strict_types=1);






/*
 * Nome de cada função que deve ser carregada caso a mesma já não tenha sido
 * definida anteriormente.
 */
foreach (\scandir(__DIR__ . DIRECTORY_SEPARATOR . "Functions") as $function_file) {
    if (
        $function_file !== "." &&
        $function_file !== ".." &&
        \function_exists(\str_replace(".php", "", $function_file)) === false
    ) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Functions" . DIRECTORY_SEPARATOR . $function_file;
    }
}
unset($function_file);