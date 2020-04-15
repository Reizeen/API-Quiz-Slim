<?php

    use Illuminate\Database\Eloquent\Model;
    
    class Temas extends Model {
        protected $table = 'temas';
        protected $primaryKey = 'name'; // Especificar que el id es la columna 'cod'.
        public $timestamps = false; // Elimina los campos de tiempo. 
        
    }

?>