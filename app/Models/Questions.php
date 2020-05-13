<?php

    use Illuminate\Database\Eloquent\Model;
    
    class Questions extends Model {
        public $timestamps = false; // Elimina los campos de tiempo. 

        // Especifica que la columna answers es un array
        protected $casts = [
            'answers' => 'array'
        ];

    }

?>