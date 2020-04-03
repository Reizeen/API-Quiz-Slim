<?php

include __DIR__ . '/Database.php';

/**
 * Clase para las consultas a la Base de Datps
 */

class QueryDB {
    
    private $db;

    /**
     * Devolver todas las temas del quiz
     */
    public function getTemas(){
        // Instancias consultas a la BD
        $db = new Database();

        // Consulta
        $sql = "SELECT cod, name FROM temas";

        // Conexion
        $db = $db->conn();
        $execute = $db->query($sql);
        $result = $execute->fetchAll(PDO::FETCH_OBJ);

        return $result;
    }

}

?>