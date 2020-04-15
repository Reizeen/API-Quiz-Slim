<?php

    use Illuminate\Database\Eloquent\Model;
    
    class Temas extends Model {
        protected $table = 'temas';
        protected $primaryKey = 'cod'; // Especificar que el id es la columna 'cod'.
        public $incrementing = false; // Especificar que el id no es numerico y no se incrementa. 
        public $timestamps = false; // Elimina los campos de tiempo. 
        
    }

?>