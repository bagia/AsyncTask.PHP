<?php

spl_autoload_register(function($class) {
    $path = realpath(__DIR__ . "/{$class}.php");
    if (file_exists($path))
        include_once($path);
});

define('ASYNC_INIT', 0);
define('ASYNC_RUNNING', 1);
define('ASYNC_DONE', 2);
define('ASYNC_DELETED', 3);