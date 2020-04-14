<?php

    use Illuminate\Database\Eloquent\Model;
    
    class Usuarios extends Model {
        protected $table = 'usuarios';
        public $timestamps = false; // Elimina los campos de tiempo. 

        
    }

?>