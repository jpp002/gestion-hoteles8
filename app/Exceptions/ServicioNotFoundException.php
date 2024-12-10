<?php

namespace App\Exceptions;

use Exception;

class ServicioNotFoundException extends Exception
{
    protected $servicioId;

    public function __construct($servicioId = null)
    {
        $this->servicioId = $servicioId;
        $message = $servicioId 
            ? "El servicio con ID {$servicioId} no existe." 
            : "El servicio no existe.";
        parent::__construct($message);
    }

    public function render($request)
    {
        return response()->json([
            'Error' => $this->getMessage(),
        ], 404);
    }
}