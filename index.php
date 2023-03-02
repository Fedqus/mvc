<?php

use core\Core;

spl_autoload_register(function ($className) {
    $path = $className.".php";
    if (is_file($path)) {
        require($path);
    }
});

$core = Core::getInstance();
$core->initialize();
$core->run();
$core->done();
