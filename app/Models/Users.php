<?php

    use Illuminate\Database\Eloquent\Model;
    
    class Users extends Model {
        public $timestamps = false; // Elimina los campos de tiempo. 
        protected $hidden = ['pass']; // Ocultar password en el JSON
    }

?>