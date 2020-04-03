<?php

    class Database {

        private $host = "localhost";
        private $username = "root";
        private $password = "";
        private $db_name = "quiz";

        // Conexion a la base de datos
        public function conn(){
            $conexion_mysql = "mysql:host=$this->host; dbname=$this->db_name";
            $conexionDB = new PDO($conexion_mysql, $this->username, $this->password);
            $conexionDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Arregla posibles errores de codificacion de caracteres UTF-8
            $conexionDB->exec("set names utf8");

            return $conexionDB;
        }
    }

?>