<?php

namespace App\Controllers;
use Reports;
use Exception;


class ReportController extends BaseController {

    /**
     * @POST
     * Añadir reporte a la Base de datos
     */
    public function setReport($request, $response, $args){
        $this->container["logger"]->debug('POST /report');
        $data = $request->getParsedBody();
        $report = new Reports();
        $report->report = $data['report'];
        $report->question_id = $data['idQuestion'];
        
        try {
            $report->save();
            return $response->withJson([ 'resp' => true, 'desc' => 'Reporte enviado satisfactoriamente'], 201);

        } catch(Exception $e){
            $this->container["logger"]->error("ERROR: {$e->getMessage()}");
            return $response->withJson([
                'error' => 1,
                'desc' => 'Error al enviar el reporte ' . $e->getMessage()], 400);
        }
    }

}

?>