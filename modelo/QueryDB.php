<?php

include __DIR__ . '/Database.php';

/**
 * Clase para las consultas a la Base de Datps
 */
class QueryDB {
    
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
        $db = null;

        return $result;
    }


    /**
     * Devolver 5 preguntas al azar según el tema
     */
    public function getQuests($tema){
        // Instancias consultas a la BD
        $db = new Database();

        // Consulta
        $sql = "SELECT * FROM preguntas WHERE temas_cod = '$tema' ORDER BY RAND() LIMIT 5";

        // Conexion
        $db = $db->conn();
        $execute = $db->query($sql);
        $result = $execute->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        return $result;
    }

}

?>