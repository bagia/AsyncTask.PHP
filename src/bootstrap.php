<?php

spl_autoload_register(function($class) {
    require_once("{$class}.php");
});

define('ASYNC_INIT', 0);
define('ASYNC_RUNNING', 1);
define('ASYNC_DONE', 2);
define('ASYNC_DELETED', 3);