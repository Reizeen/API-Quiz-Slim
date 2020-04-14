<?php

    use Illuminate\Database\Eloquent\Model;
    
    class Preguntas extends Model {
        protected $table = 'preguntas';
        public $timestamps = false; // Elimina los campos de tiempo. 
        
    }

?>