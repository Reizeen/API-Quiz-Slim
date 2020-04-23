<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../model/Temas.php';
require __DIR__ . '/../model/Preguntas.php';
require __DIR__ . '/../model/Usuarios.php';
require __DIR__ . '/../model/Puntos.php';

$config = include( __DIR__ . '/../public/config.php');

$app = new \Slim\App([
    'debug'=> true,
    'settings' => $config
]);

/**
 * Service factory for the ORM Eloquent
 */
$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('LOG');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();






/*===============================================
  ===================== GET =====================
  =============================================== */


/**
 * Probar consulta Eloquente para user
 */
$app->get('/test/{name}', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET test/user');

	echo "<h1>Test User</h1><p>Buscando ... [".$args['name'] ."]";

	$name=$args['name'];
	// https://stackoverflow.com/questions/39270169/laravel-5-eloquent-how-to-get-raw-sql-that-is-being-executed-with-binded-data?noredirect=1&lq=1
	$query= Users::where("name", $name);
	echo "<p>SQL : </p><pre>";
	echo $query->toSql();
	echo "</pre>";
	
    $user = Users::where("name", $name)->first();
	$recname = $user['name'];
	echo "<pre>";
	print_r($user);
	echo "</pre>";
	echo "<p>User []=$recname</p>";
	
	
	$name=$args['name'];
	// https://stackoverflow.com/questions/39270169/laravel-5-eloquent-how-to-get-raw-sql-that-is-being-executed-with-binded-data?noredirect=1&lq=1
	$query= Users::whereRaw("name='$name'");
	echo "<p>rawSQL : </p><pre>";
	echo $query->toSql();
	echo "</pre>";
	
    $user = Users::where("name", $name)->first();
	$recname = $user['name'];
	echo "<pre>";
	print_r($user);
	echo "</pre>";
	echo "<p>User []=$recname</p>";	
});




/**
 * Correr la API
 */
$app->run();