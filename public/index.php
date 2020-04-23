<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../model/Themes.php';
require __DIR__ . '/../model/Questions.php';
require __DIR__ . '/../model/Users.php';
require __DIR__ . '/../model/Points.php';

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
 * Encriptar la contraseña
 */
function encriptPassword($password){
    $password_encript = password_hash($password, PASSWORD_DEFAULT, array("cost"=>15));
    return $password_encript;
}

/**
 * Crear la puntuacion inicial para el usuario una vez registrado
 */
function initialScore($user_id){
    $points = new Points();
    $points->points = 0;
    $points->user_id = $user_id;
    $points->save();
}

/**
 * Comprobar si el nombre de usuario esta registrado
 */
function checkUser($user_name){
    $user = Users::where('name', $user_name)->first();
    $name = $user['name'];
    if (strcasecmp($name, $user_name) == 0)
        return true;
    return false;
}

/**
 * Comprobar si el email ya esta registrado
 */
function checkEmail($user_email){
    $user = Users::where('email', $user_email)->first();
    $email = $user['email'];
    if (strcasecmp($email, $user_email) == 0)
        return true;
    return false;
}


/**
 * Registro de usuarios con la contraseña encriptada.
 * Se crea la puntuacion para el usuario con 0 puntos. 
 */
$app->post('/signup', function(Request $request, Response $response, $args) {
   
    $this["logger"]->debug('POST /signup');
    $data = $request->getParsedBody();
    $user_name = $data['name'];
    $user_email = $data['email'];
    $password = $data['pass'];

    try {
        if (checkUser($user_name)){
            return $response->withJson([
                'resp' => false,
                'desc' => 'Usuario ya registrado'], 200);
        }
        if (checkEmail($user_email)){
            return $response->withJson([
                'resp' => false,
                'desc' => 'Email ya registrado'], 200);
        }
        $user = new Users();
        $user->name = $user_name;
        $user->email = $user_email;
        $user->pass = encriptPassword($password);
        $user->save();

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error al registrar al usuario ' . $e->getMessage()], 400);
    }
    try {
        // Crear la puntuacion inicial del usuario
        $user_id = $user->id;
        initialScore($user_id);

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                   'error' => 1,
                   'desc' => 'Error al registrar la puntuacion inicial del usuario ' . $e->getMessage()], 400);
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
        $user = Users::where("name", $user_login)->first();
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
$app->put('/user', function(Request $request, Response $response, $args) {
    
    $this["logger"]->debug('PUT /user');
    $data = $request->getParsedBody();
    $user_name = $data['user'];
    $pass_actual = $data['pass'];
    $new_pass = $data['new_pass'];

    try {
        $user = Users::where("name", $user_name)->first();
        $password_encript = $user['pass'];

        if (password_verify($pass_actual, $password_encript)){
            $user->pass = encriptPassword($new_pass);
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
        return $response->withJson(Themes::all());
        
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
$app->get('/quiz/{theme}', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET /quiz');

    $theme = $args['theme'];
    try {
        return $response->withJson(Questions::all()->where('theme_cod', $theme)->random(5));

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});


/**
 * Consultar puntuaciones de los usuarios
 */
$app->get('/puntos', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET /puntos');

    try {
        return $response->withJson(Points::all());

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
        $user = Users::where('name', $user_name)->first();
        return $response->withJson(Points::where('user_id', $user['id'])->first());

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
    }
});


/**
 * Consultar todas las preguntas de un usuario especifico
 */
$app->get('/preguntas/{user}', function(Request $request, Response $response, $args){

    $this["logger"]->debug('GET /preguntas');

    $user_name = $args['user'];
    try {
        $user = Users::where('name', $user_name)->first();
        return $response->withJson(Questions::all()->where('user_id', $user->id));

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
        $question = Questions::where('id', $id)->first();
        
        if ($question != null)
            return $response->withJson($question);

        return $response->withJson([
            'resp' => false,
            'desc' => 'Pregunta no encontrada'], 401);
        
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
        return $response->withJson(Users::where('name', $user)->first());

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
    $question = new Questions();
    $question->question = $data['question'];
    $question->respcorrect = $data['respcorrect'];
    $question->respaltone = $data['respaltone'];
    $question->respalttwo = $data['respalttwo'];
    $question->respaltthree = $data['respaltthree'];
    $question->user_id = $data['id'];

    $theme_name = $data['theme'];
    $theme = Themes::where("name", $theme_name)->first();
    $question->theme_cod = $theme['cod'];

    try {
        $question->save();
        return $response->withJson([
            'resp' => true,
            'desc' => 'Pregunta guardada satisfactoriamente'], 201);

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error al guardar la pregunta ' . $e->getMessage()], 400);
    }
});



/*===============================================
  ==================== PUT ======================
  =============================================== */

/**
 * Modificar pregunta por parte del usuario.  
 */
$app->put('/pregunta', function(Request $request, Response $response, $args) {
    
    $this["logger"]->debug('PUT /pregunta');
    $data = $request->getParsedBody();
    $quest = $data['question'];
    $respcorrect = $data['respcorrect'];
    $respaltone = $data['respaltone'];
    $respalttwo= $data['respalttwo'];
    $respaltthree = $data['respaltthree'];
    $id = $data['id'];

    $theme_name = $data['theme'];
    $theme = Themes::where("name", $theme_name)->first();
    $theme_cod = $theme['cod'];

    try {
        $question = Questions::where('id', $id)->first();
        $question->question = $quest;
        $question->respcorrect = $respcorrect;
        $question->respaltone = $respaltone;
        $question->respalttwo = $respalttwo;
        $question->respaltthree = $respaltthree;
        $question->theme_cod = $theme_cod;
        $question->save();

        return $response->withJson([
            'resp' => true,
            'desc' => 'Pregunta modificada satisfactoriamente'], 201);

    } catch (Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
                'error' => 1,
                'desc' => 'Error al modificar la pregunta ' . $e->getMessage()], 400);
    }
});


/**
 * Modificar puntos del usuario
 */
$app->put('/puntos', function(Request $request, Response $response, $args) {

    $this["logger"]->debug('PUT /puntos');

    $data = $request->getParsedBody();
    $user = $data['id'];
    $pointsObtained = $data['points'];

    try {
        $points = Points::where('user_id', $user)->first();
        $currentPoints = $points->points;

        $totalPoints = $pointsObtained + $currentPoints;
        $points->points = $totalPoints;
        $points->save();

        return $response->withJson([
            'resp' => true,
            'desc' => 'Puntos añadidos satisfactoriamente'], 201);

    } catch(Exception $e){
        $this["logger"]->error("ERROR: {$e->getMessage()}");
        return $response->withJson([
            'error' => 1,
            'desc' => 'Error al modificar los puntos ' . $e->getMessage()], 400);
    }
    


});












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