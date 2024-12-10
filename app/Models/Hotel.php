<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;


    protected $table = 'hoteles';

    protected $fillable = ['nombre', 'direccion', 'telefono', 'email', 'sitioWeb'];

    //Relaciones
    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class);
    }

    // Muchos a Muchos
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class);
    }
}