<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../model/Temas.php';
require __DIR__ . '/../model/Preguntas.php';
require __DIR__ . '/../model/Usuarios.php';
require __DIR__ . '/../model/Puntos.php';

$config = include('config.php');

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
  =============== AUTENTIFICACIÓN ===============
  =============================================== */

/**
 * Registro de usuarios con la contraseña encriptada.
 * Se registra la informacion inicial de los puntos con 0 puntos 
 */
$app->post('/signup', function(Request $request, Response $response, $args) {
   
    $this["logger"]->debug('POST /signup');

    $data = $request->getParsedBody();
    $user_name = $data['name'];
    $user_email = $data['email'];

    try {
        $user = new Usuarios();
        $user->name = $user_name;
        $user->email = $user_email;

        // Encriptacion de la contraseña con HASH
        $password = $data['pass'];
        $password_encript = password_hash($password, PASSWORD_DEFAULT, array("cost"=>15));
        $user->pass = $password_encript;
        $user->save();

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error al registrar al usuario' . $e->getMessage()], 400);
    }

    try {
        // Crear la puntuacion inicial del usuario
        $puntos = new Puntos();
        $puntos->puntos = 0;
        $puntos->usuarios_name = $user_name;
        $puntos->save();

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                   'error' => 1,
                   'desc' => 'Error al registrar la puntuacion inicial del usuario' . $e->getMessage()], 400);
   }

   return $response->withJson([
    'resp' => true,
    'desc' => 'Usuario registrado satisfactoriamente'], 201);

});


/**
 * Login de usuarios, comprobando con la contraseña encriptada. 
 */
$app->post('/signin', function(Request $request, Response $response, $args) {

    $this["logger"]->debug('POST /signin');

    $data = $request->getParsedBody();
    $user_login = $data['name'];
    $pass_login = $data['pass'];

    try {
        $user = Usuarios::all()->where("name", $user_login)->first();
        $pass_user = $user['pass'];

        // Comprobar contraseña con la contraseña encriptada. 
        if (password_verify($pass_login, $pass_user)){
            return $response->withJson([
                'resp' => true,
                'desc' => 'Inicio de sesion satisfactorio'], 200);
        }

        return $response->withJson([
            'resp' => false,
            'desc' => 'Usuario no verificado'], 401);
        
    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});


/**
 * Cambiar contraseña por parte del usuario
 */
$app->put('/user/{user_name}', function(Request $request, Response $response, $args) {
    
    $this["logger"]->debug('PUT /user');

    $user_name = $args['user_name'];
    $data = $request->getParsedBody();
    $pass = $data['pass'];
    $new_pass = $data['new_pass'];

    try {
        $user = Usuarios::all()->where("name", $user_name)->first();
        $pass_user = $user['pass'];

        // Comprobar contraseña con la contraseña encriptada. 
        if (password_verify($pass, $pass_user)){

            // Encriptacion de la contraseña con HASH
            $password_encript = password_hash($new_pass, PASSWORD_DEFAULT, array("cost"=>15));
            $user->pass = $password_encript;
            $user->save();

            return $response->withJson([
                'resp' => true,
                'desc' => 'Contraseña cambiada satisfactoriamente'], 201);       
        } 

        return $response->withJson([
            'resp' => false,
            'desc' => 'Contraseña actual incorrecta'], 401);

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});



/*===============================================
  ===================== GET =====================
  =============================================== */

/**
 * Mostrar todos los temas
 */
$app->get('/temas', function(Request $request, Response $response, $args) {

    $this["logger"]->debug('GET /temas');

    try {
        return $response->withJson(Temas::all());
        
    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});

/**
 * Consultar 5 preguntas aleatorias segun el tema especificado 
 * para el desarrollo del juego quiz.  
 */
$app->get('/quiz/{tema}', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET /quiz');

    $tema = $args['tema'];
    try {
        return $response->withJson(Preguntas::all()->where('temas_cod', $tema)->random(5));

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});

/**
 * Consultar punrtuaciones de los usuarios
 */
$app->get('/puntos', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET /puntos');

    try {
        return $response->withJson(Puntos::all());

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});

/**
 * Consultar puntos de un usuario especifico
 */
$app->get('/puntos/{user}', function(Request $request, Response $response, $args){
    
    $this["logger"]->debug('GET /puntos');

    $user_name = $args['user'];
    try {
        return $response->withJson(Puntos::all()->where('usuarios_name', $user_name)->first());

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});

/**
 * Consultar puntos de un usuario especifico
 */
/*$app->get('/puntos/{user}', function(Request $request, Response $response, $args){
    
    $user_name = $args['user'];

    try {
        return $response->withJson(Puntos::all()->where('usuarios_name', $user_name)->first());
    } catch (Exception $e){
         return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});*/

/**
 * Consultar preguntas de un usuario especifico
 */
$app->get('/preguntas/{usuario}', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET /preguntas');

    $user = $args['usuario'];
    try {
        return $response->withJson(Preguntas::all()->where('user_name', $user));

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});

/**
 * Consultar una pregunta especificoa
 */
$app->get('/pregunta/{id}', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET /pregunta');

    $id = $args['id'];
    try {
        return $response->withJson(Preguntas::all()->where('id', $id)->first());

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});


/**
 * Mostrar informacion del usuario
 */
$app->get('/user/{usuario}', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET /user');

    $user = $args['usuario'];
    try {
        return $response->withJson(Usuarios::all()->where('name', $user));

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
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
$app->post('/pregunta', function(Request $request, Response $response, $args) {

    $this["logger"]->debug('POST /preguntas');

    $data = $request->getParsedBody();
    $question = new Preguntas();
    $question->pregunta = $data['pregunta'];
    $question->respcorrect = $data['respuesta_correcta'];
    $question->respuno = $data['respuesta_uno'];
    $question->respdos = $data['respuesta_dos'];
    $question->resptres = $data['respuesta_tres'];
    $question->user_name = $data['user'];

    $tema_name = $data['tema'];
    $tema = Temas::all()->where("name", $tema_name)->first();
    $question->temas_cod = $tema['cod'];

    try {
        $question->save();
        return $response->withJson([
            'resp' => true,
            'desc' => 'Pregunta guardada satisfactoriamente'], 201);

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error al guardar la pregunta' . $e->getMessage()], 400);
    }
});



/*===============================================
  ==================== PUT ======================
  =============================================== */

/**
 * Modificar pregunta por parte del usuario.  
 */
$app->put('/pregunta/{id}', function(Request $request, Response $response, $args) {
    
    $this["logger"]->debug('PUT /pregunta');

    $id = $args['id'];
    $data = $request->getParsedBody();
    $pregunta = $data['pregunta'];
    $respuesta_correcta = $data['respuesta_correcta'];
    $respuesta_uno = $data['respuesta_uno'];
    $respuesta_dos= $data['respuesta_dos'];
    $respuesta_tres = $data['respuesta_tres'];

    $tema_name = $data['tema'];
    $tema = Temas::all()->where("name", $tema_name)->first();
    $cod_tema = $tema['cod'];

    try {
        $question = Preguntas::all()->where('id', $id)->first();
        $question->pregunta = $pregunta;
        $question->respcorrect = $respuesta_correcta;
        $question->respuno = $respuesta_uno;
        $question->respdos = $respuesta_dos;
        $question->resptres = $respuesta_tres;
        $question->temas_cod = $cod_tema;
        $question->save();

        return $response->withJson([
            'resp' => true,
            'desc' => 'Pregunta modificada satisfactoriamente'], 201);

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error al modificar la pregunta' . $e->getMessage()], 400);
    }
});


/**
 * Modificar puntos del usuario
 */
$app->put('/puntos', function(Request $request, Response $response, $args) {

    $this["logger"]->debug('PUT /puntos');

    $data = $request->getParsedBody();
    $user = $data['user'];
    $puntos_obtenidos = $data['puntos'];

    try {
        $puntos = Puntos::all()->where('usuarios_name', $user)->first();
        $puntos_actuales = $puntos->puntos;

        $puntos_totales = $puntos_obtenidos + $puntos_actuales;
        $puntos->puntos = $puntos_totales;
        $puntos->save();

        return $response->withJson([
            'resp' => true,
            'desc' => 'Puntos añadidos satisfactoriamente'], 201);

    } catch(Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
            'error' => 1,
            'desc' => 'Error al modificar los puntos' . $e->getMessage()], 400);
    }
    


});

$app->run();