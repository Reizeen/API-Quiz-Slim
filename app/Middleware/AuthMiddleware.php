<?php

namespace App\Middleware;

class AuthMiddleware extends Middleware {

    public function __invoke($request, $response, $next)
    {
        $token = $request->getHeader('token');
        $auth = $this->container->auth->checkSession($token);

        if(!$auth)
            return $response->withJson(['resp' => false, 'desc' => 'Usuario no verificado'], 401);
        
        $response = $next($request, $response);
        return $response;
    }
}

?>