<?php

namespace App\Controllers;
use Exception;
use Questions;
use Themes;

class QuestionController extends BaseController {


    /**
     * @GET
     * Muestra preguntas aleatorias de un tema para el juego
     */
    public function gameQuiz($request, $response, $args){
        $this->container["logger"]->debug('GET /quiz');
        $theme = $args['theme'];

        try {
            return $response->withJson(Questions::all()->where('theme_cod', $theme)->random(5));

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }


    /**
     * @GET
     * Preguntas de un usuario especifico
     */
    public function getQuestions($request, $response, $args){
        $this->container["logger"]->debug('GET /preguntas');
        $user_id = $args['id_user'];

        try {
            return $response->withJson(Questions::all()->where('user_id', $user_id));
    
        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }


    /**
     * @GET
     * Consultar una pregunta especifica.
     */
    public function getQuestion($request, $response, $args){
        $this->container["logger"]->debug('GET /pregunta');
        $id = $args['id'];

        try {
            $question = Questions::where('id', $id)->first();
            
            if ($question != null)
                return $response->withJson($question);

            return $response->withJson([
                'resp' => false, 'desc' => 'Pregunta no encontrada'], 401);
            
        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error procesando petición ' . $e->getMessage()], 400);
        }
    }


    /**
     * @POST
     * Añadir una pregunta
     */
    public function addQuestion($request, $response, $args){
        $this->container["logger"]->debug('POST /preguntas');

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
            return $response->withJson(['resp' => true, 'desc' => 'Pregunta guardada satisfactoriamente'], 201);

        } catch (Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error al guardar la pregunta ' . $e->getMessage()], 400);
        }
    }


    /**
     * @PUT
     * Modificar pregunta
     */
    public function setQuestion($request, $response, $args){
        $this->container["logger"]->debug('PUT /pregunta');
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
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                    'error' => 1,
                    'desc' => 'Error al modificar la pregunta ' . $e->getMessage()], 400);
        }
    }
}

?>