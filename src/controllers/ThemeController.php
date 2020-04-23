<?php 

namespace App\Controllers;
use Exception;
use Themes;

class ThemeController extends BaseController {

    /**
     * @GET
     * Mostrar todos los temas
     */
    public function getThemes($request, $response, $args){
        $this->container["logger"]->debug('GET /temas');
        try {
            return $response->withJson(Themes::all());
            
        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }
}

?>