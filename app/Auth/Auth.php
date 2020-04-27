<?php

namespace App\Auth;
use Illuminate\Support\Str;
use Users;

class Auth {

    /**
     * Verificando login
     */
    public function checkLogin($user, $password){
        $user = Users::where("name", $user)->first();
        
        if(!$user)
            return false;

        if(password_verify($password, $user->pass)){
            return true;
        }

        return false;
    }


    /**
     * Verificando sesion con token 
     */
    public function checkSession($token){
        $user = Users::where("token", $token)->first();
        if(!$user)
            return false;
        return true;
    }


    /**
     * Generar token
     */
    public function generateToken(){
        return  Str::random(60);
    }


    /**
     * Verificando si nombre de usuario está registrado
     */
    public function checkUser($user_name){
        $user = Users::where('name', $user_name)->first();
        $name = $user['name'];

        if (strcasecmp($name, $user_name) == 0)
            return true;

        return false;
    }


    /**
     * Verificando si email está registrado
     */
    public function checkEmail($user_email){
        $user = Users::where('email', $user_email)->first();
        $email = $user['email'];

        if (strcasecmp($email, $user_email) == 0)
            return true;
            
        return false;
    }


    /**
     * Encriptar la contraseña
     */
    public function encriptPassword($password){
        $password_encript = password_hash($password, PASSWORD_DEFAULT, array("cost"=>15));
        return $password_encript;
    }
    
}

?>