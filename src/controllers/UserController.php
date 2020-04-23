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
     * Login de usuario
     */
    public function login($request, $response, $args){
        $this->container["logger"]->debug('POST /signin');
        $data = $request->getParsedBody();
        $user_login = $data['name'];
        $pass_login = $data['pass'];

        try {
            $user = Users::where("name", $user_login)->first();
            $pass_user = $user['pass'];

            // Comprobar contraseña con la contraseña encriptada. 
            if (password_verify($pass_login, $pass_user))
                return $response->withJson($user, 200);
            
            return $response->withJson(['resp' => false, 'desc' => 'Usuario no verificado'], 401);
            
        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }


    /**
     * Encriptar la contraseña
     */
    function encriptPassword($password){
        $password_encript = password_hash($password, PASSWORD_DEFAULT, array("cost"=>15));
        return $password_encript;
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
            if ($this->checkUser($user_name))
                return $response->withJson([ 'resp' => false, 'desc' => 'Usuario ya registrado'], 200);
            
            if ($this->checkEmail($user_email))
                return $response->withJson([ 'resp' => false, 'desc' => 'Email ya registrado'], 200);
            
            $user = new Users();
            $user->name = $user_name;
            $user->email = $user_email;
            $user->pass = $this->encriptPassword($password);
            $user->save();

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error al registrar al usuario ' . $e->getMessage()], 400);
        }

        try {
            // Puntuacion inicial del usuario
            $user_id = $user->id;
            $points = new Points();
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
            $user = Users::where("name", $user_name)->first();
            $password_encript = $user['pass'];

            if (password_verify($pass_actual, $password_encript)){
                $user->pass = $this->encriptPassword($new_pass);
                $user->save();

                return $response->withJson(['resp' => true, 'desc' => 'Contraseña cambiada satisfactoriamente'], 201);       
            } 
            return $response->withJson(['resp' => false, 'desc' => 'Contraseña actual incorrecta'], 401);

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }
}

?>
