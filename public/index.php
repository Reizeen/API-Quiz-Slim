<?php

require __DIR__ . '/../vendor/autoload.php';

$config = include(__DIR__ . '/../src/settings/config.php');
$app = new \Slim\App([
    'debug'=> true,
    'settings' => $config
]);

require __DIR__ . '/../src/dependencies/dependencies.php';
require __DIR__ . '/../src/routes/routes.php';

$app->run();