<?php

use Slam\Application;

$rootDir = dirname(__DIR__);

require($rootDir . '/vendor/autoload.php');

$app = new Application([
    'root_dir' => $rootDir
]);

$app();
