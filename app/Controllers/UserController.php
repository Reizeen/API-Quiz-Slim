<?php

namespace App\Controllers;
use Exception;
use Users;
use Points;

class UserController extends BaseController {

    /**
     * @GET
     * Mostrar info del usuario
     */
    public function getUser($request, $response, $args){
        $this->container["logger"]->debug('GET /user');
        $id = $args['id'];

        try {
            return $response->withJson(Users::where('id', $id)->first(), 200);

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }


    /**
     * @POST
     * Comprobar sesion con token
     */
    public function session($request, $response, $args){
        $this->container["logger"]->debug('POST /session');
        $data = $request->getParsedBody();
        $id = $data['id'];
        $token = $data['token'];

        try {
            $auth = $this->container->auth->checkToken($id, $token);

            if ($auth)
                return $response->withJson([ 'resp' => true, 'desc' => 'Hay sesion iniciada'], 200);
            return $response->withJson([ 'resp' => false, 'desc' => 'No hay sesion iniciada'], 200);


        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición' . $e->getMessage()], 400);
        }

    }


    /**
     * @POST
     * Login de usuario
     */
    public function login($request, $response, $args){
        $this->container["logger"]->debug('POST /signin');
        $data = $request->getParsedBody();
        $user_login = $data['name'];
        $pass_login = $data['pass'];

        try {
            $auth = $this->container->auth->checkLogin($user_login, $pass_login);

            if($auth){
                $user = Users::where("name", $user_login)->first();
                $user->token = $this->container->auth->generateToken();
                $user->save();
                return $response->withJson($user, 200);
            }
            
            return $response->withJson(['resp' => false, 'desc' => 'Usuario no verificado'], 401);
            
        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }


    /**
     * @POST
     * Registro de usuarios
     */
    public function register($request, $response, $args){
        $this->container["logger"]->debug('POST /signup');
        $data = $request->getParsedBody();
        $user_name = $data['name'];
        $user_email = $data['email'];
        $password = $data['pass'];

        try {
            if ($this->container->auth->checkUser($user_name))
                return $response->withJson([ 'resp' => false, 'desc' => 'Usuario ya registrado'], 200);
            
            if ($this->container->auth->checkEmail($user_email))
                return $response->withJson([ 'resp' => false, 'desc' => 'Email ya registrado'], 200);
            
            $user = new Users;
            $user->name = $user_name;
            $user->email = $user_email;
            $user->pass = $this->container->auth->encriptPassword($password);
            $user->save();

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error al registrar al usuario ' . $e->getMessage()], 400);
        }

        try {
            // Registro de la puntuacion incial del usuario
            $user_id = $user->id;
            $points = new Points;
            $points->points = 0;
            $points->user_id = $user_id;
            $points->save();

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error al registrar la puntuacion inicial del usuario ' . $e->getMessage()], 400);
        }
        
        return $response->withJson([
            'resp' => true, 'desc' => 'Usuario registrado satisfactoriamente'], 201);
    }

    /**
     * @POST 
     * Enviar correo al usuario para recuperar la contraseña
     */
    public function sendEmail($request, $response, $args){
        $this->container["logger"]->debug('POST /email');
        $data = $request->getParsedBody();
        $email = $data['email'];

        try {
            $auth = $this->container->auth->checkEmail($email);

            if (!$auth)
                return $response->withJson(['resp' => false, 'desc' => 'Email no registrado'], 200);
            
            $user = Users::where("email", $email)->first();
            $new_pass = $user->pass = $this->container->auth->generatePassword();
            $user->pass = $this->container->auth->encriptPassword($new_pass);
            $user->save();
            
            $subject = "Recuperación de la contraseña";
            $message = "
            <html>
            <head>
              <title>" . $subject . "</title>
            </head>
            <body>
                <table>
                    <tr>
                        <td><span style='font-size:15px'>La nueva contraseña para <strong>" . $user->name . "</strong> es: <strong>". $new_pass . "</strong><br/></td>
                    </tr>
                    <tr>
                        <td><em>Cuando inicie sesion no olvide cambiar la contraseña en su perfil.</em></span></td>
                    </tr>
                </table>
                </body>
            </html>";
            $headers = "From: QUIZ <no-reply@quizeric.com>" . "\r\n";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";        

            mail($email, $subject, $message, $headers);

            return $response->withJson(['resp' => true, 'desc' => 'Enviado: accede a tu Email'], 200);
        
        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición' . $e->getMessage()], 400);
        }
    }


    /**
     * @PUT
     * Cambiar password al usuario
     */
    public function changePassword($request, $response, $args){
        $this->container["logger"]->debug('PUT /user');
        $data = $request->getParsedBody();
        $user_name = $data['user'];
        $pass_actual = $data['pass'];
        $new_pass = $data['new_pass'];

        try {
            // Verificar contraseña del usuario
            $auth = $this->container->auth->checkLogin($user_name, $pass_actual);

            if($auth){
                $user = Users::where("name", $user_name)->first();
                $user->pass = $this->container->auth->encriptPassword($new_pass);
                $user->save();
                return $response->withJson(['resp' => true, 'desc' => 'Contraseña cambiada satisfactoriamente'], 201);       
            }
    
            return $response->withJson(['resp' => false, 'desc' => 'Contraseña actual incorrecta'], 200);

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }
}

?>
