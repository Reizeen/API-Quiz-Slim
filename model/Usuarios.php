<?php

    use Illuminate\Database\Eloquent\Model;
    
    class Usuarios extends Model {
        protected $table = 'usuarios';
        protected $primaryKey = 'name'; // Especificar que el id es la columna 'name'.
        public $incrementing = false; // Especificar que el id no es numerico y no se incrementa. 
        public $timestamps = false; // Elimina los campos de tiempo. 

        
    }

?>