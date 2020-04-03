<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../modelo/QueryDB.php';

$app = new \Slim\App(['debug'=>true]);

// Instancias la clase de las consultas
$query = new QueryDB();

/**
 * Todos los temas
 */
$app->get('/temas', function(Request $request, Response $response, $args) use($query){
    try {
        return $response->withJson($query->getTemas());
    } catch (Exception $e){
         return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});

$app->run();