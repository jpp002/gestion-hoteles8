<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Huesped extends Model
{
    use HasFactory;

    protected $table = 'huespedes';

    protected $fillable = ['nombre', 'apellido', 'dniPasaporte', 'fechaCheckin', 'fechaCheckout', 'habitacion_id'];

    //Relacion
    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }
}