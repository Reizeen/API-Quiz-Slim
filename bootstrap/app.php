<?php

/**
 * Dependencies
 */
require __DIR__ . '/../vendor/autoload.php';


/**
 * Config Slim
 */
$settings = include(__DIR__ . '/settings.php');


/**
 * The application
 */
$app = new \Slim\App([
    'debug'=> true,
    'settings' => $settings
]);



$container = $app->getContainer();

/**
 * View configuration
 */
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../resources/views', [
        'cache' => false
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension (
        $container->router,
        $container->request->getUri()
    ));
    return $view;
};


/**
 * Log configuration
 */
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('LOG');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};


/**
 * Service factory for the ORM Eloquent
 */
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();


/**
 * Controllers
 */
$container['UserController'] = function($c){
    return new \App\Controllers\UserController($c);
};

$container['ThemeController'] = function($c){
    return new \App\Controllers\ThemeController($c);
};

$container['QuestionController'] = function($c){
    return new \App\Controllers\QuestionController($c);
};

$container['PointController'] = function($c){
    return new \App\Controllers\PointController($c);
};

$container['ReportController'] = function($c){
    return new \App\Controllers\ReportController($c);
};


/**
 * Authentication
 */
$container['auth'] = function($c){
    return new \App\Auth\Auth;
};


/**
 * Routes
 */
require __DIR__ . '/../app/Routes/routes.php';

?>