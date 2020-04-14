<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../model/Temas.php';
require __DIR__ . '/../model/Preguntas.php';
require __DIR__ . '/../model/Usuarios.php';

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



/*===============================================
  =============== AUTENTIFICACIÓN ===============
  =============================================== */

/**
 * Registro de usuarios con la contraseña encriptada. 
 */
$app->post('/signup', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $user = new Usuarios();
    $user->name = $data['name'];
    $user->email = $data['email'];

    // Encriptacion de la contraseña con HASH
    $password = $data['pass'];
    $password_encript = password_hash($password, PASSWORD_DEFAULT, array("cost"=>15));
    $user->pass = $password_encript;

    try {
        $user->save();
        return $response->withJson([
            'signout' => true,
            'desc' => 'Usuario registrado satisfactoriamente'], 201);

    } catch (Exception $e){
         return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error al registrar al usuario' . $e->getMessage()], 400);
    }
});


/**
 * Login de usuarios, comprobando con la contraseña encriptada. 
 */
$app->post('/signin', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $user_login = $data['name'];
    $pass_login = $data['pass'];

    try {
        $user = Usuarios::all()->where("name", $user_login)->first();
        $pass_user = $user['pass'];

        // Comprobar contraseña con la contraseña encriptada. 
        if (password_verify($pass_login, $pass_user)){
            return $response->withJson([
                'signin' => true,
                'desc' => 'Inicio de sesion satisfactorio'], 200);
        } else {
            return $response->withJson([
                'signin' => false,
                'desc' => 'Usuario no verificado'], 401);
        }

    } catch (Exception $e){
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});



/*===============================================
  ===================== GET =====================
  =============================================== */

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




/*===============================================
  ==================== POST =====================
  =============================================== */

/**
 * Registro de pregunta por parte del usuario.  
 */
$app->post('/quest', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $question = new Preguntas();
    $question->pregunta = $data['pregunta'];
    $question->respcorrect = $data['respuesta_correcta'];
    $question->respuno = $data['respuesta_uno'];
    $question->respdos = $data['respuesta_dos'];
    $question->resptres = $data['respuesta_tres'];
    $question->usarios_name = $data['user'];

    $tema_name = $data['tema'];
    $tema = Temas::all()->where("name", $tema_name)->first();
    $question->temas_cod = $tema['cod'];

    try {
        $question->save();
        return $response->withJson([
            'signout' => true,
            'desc' => 'Pregunta guardada satisfactoriamente'], 201);

    } catch (Exception $e){
         return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error al guardar la pregunta' . $e->getMessage()], 400);
    }
});

$app->run();