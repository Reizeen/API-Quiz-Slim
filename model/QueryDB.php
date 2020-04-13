<?php

include __DIR__ . '/Database.php';

/**
 * Clase para consultas muy especificas a la Base de Datos
 * donde es imprescindible utilizar SQL. 
 * El resto de consultas es realizado con el ORM Eloquent
 */
class QueryDB {
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