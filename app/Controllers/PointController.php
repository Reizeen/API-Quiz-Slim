<?php

namespace App\Controllers;
use Illuminate\Database\Capsule\Manager as DB;
use Exception;
use Points;

class PointController extends BaseController {

    /**
     * @GET
     * Muestra la puntuacion de todos los usuarios
     */
    public function getPoints($request, $response, $args){
        $this->container["logger"]->debug('GET /puntos');

        try {
            $points = DB::table('points')
                        ->join('users', 'users.id', '=', 'points.user_id')
                        ->select('points.id', 'points.points', 'users.name')
                        ->orderBy('points.points', 'DESC')
                        ->get();

            return $response->withJson($points);
    
        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }


    /**
     * @GET 
     * Muestra la puntuacion de un usuario determinado
     */
    public function getPoint($request, $response, $args){
        $this->container["logger"]->debug('GET /puntos');
        $user_id = $args['id_user'];

        try {
            $points = DB::table('points')
                        ->where('user_id', $user_id)
                        ->select('points.points')
                        ->get();

            return $response->withJson($points);

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }


    /**
     * @PUT
     * Modifica la puntuacion de un usuario
     */
    public function setPoints($request, $response, $args){
        $this->container["logger"]->debug('PUT /puntos');
        $data = $request->getParsedBody();
        $user = $data['id'];
        $pointsObtained = $data['points'];

        try {
            $points = Points::where('user_id', $user)->first();
            $currentPoints = $points->points;
            $totalPoints = $pointsObtained + $currentPoints;
            $points->points = $totalPoints;
            $points->save();

            return $response->withJson([ 'resp' => true, 'desc' => 'Puntos añadidos satisfactoriamente'], 201);

        } catch(Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                'error' => 1,
                'desc' => 'Error al modificar los puntos ' . $e->getMessage()], 400);
        }
    }

}

?>