<?php

namespace App\Exceptions;

use Exception;

class HabitacionNotFoundException extends Exception
{
    protected $habitacionId;

    public function __construct($habitacionId = null)
    {
        $this->habitacionId = $habitacionId;
        $message = $habitacionId 
            ? "La habitacion con ID {$habitacionId} no existe." 
            : "La habitacion no existe.";
        parent::__construct($message);
    }

    public function render($request)
    {
        return response()->json([
            'Error' => $this->getMessage(),
        ], 404);
    }
}