<?php

namespace App\Exceptions;

use Exception;

class HotelNotFoundException extends Exception
{
    protected $hotelId;

    public function __construct($hotelId = null)
    {
        $this->hotelId = $hotelId;
        $message = $hotelId 
            ? "El hotel con ID {$hotelId} no existe." 
            : "El hotel no existe.";
        parent::__construct($message);
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 404);
    }
}