<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../model/Temas.php';
require __DIR__ . '/../model/Preguntas.php';

$config = include('config.php');

$app = new \Slim\App([
    'debug'=> true,
    'settings' => $config
]);



/**
 * Service factory for the ORM Eloquent
 */
$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();



/**
 * Todos los temas
 */
$app->get('/temas', function(Request $request, Response $response, $args) {
    try {
        return $response->withJson(Temas::all());
    } catch (Exception $e){
         return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});

/**
 * Consultar 5 preguntas aleatorias segun el tema especificado. 
 */
$app->get('/quest/{tema}', function(Request $request, Response $response, $args){

    $tema = $args['tema'];
    try {
        return $response->withJson(Preguntas::all()->where('temas_cod', $tema)->random(5));
    } catch (Exception $e){
         return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});

$app->run();