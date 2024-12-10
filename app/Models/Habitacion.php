<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    use HasFactory;

    protected $table = 'habitaciones';

    protected $fillable = ['numero', 'tipo', 'precioNoche', 'hotel_id'];

    //Relaciones
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function huespedes()
    {
        return $this->hasMany(Huesped::class);
    }

    public function isDisponible()
    {
        // Define la capacidad máxima según el tipo de habitación
        $capacidades = [
            'simple' => 1,          // Habitación para una persona
            'doble' => 2,           // Habitación para dos personas
            'suite' => 4,           // Habitación premium, hasta 4 personas
            'familiar' => 6,        // Habitación para familias
            'deluxe' => 2,          // Habitación doble con comodidades adicionales
            'economica' => 1,       // Habitación sencilla con precio reducido
            'presidencial' => 6,    // Habitación de lujo con capacidad máxima
            'triple' => 3,          // Habitación con tres camas individuales
            'compartida' => 8,      // Habitación tipo hostal, con camas múltiples
        ];

        $capacidadMaxima = $capacidades[$this->tipo] ?? 1;


        // Contar los huéspedes actuales que no han hecho checkout
        $huespedesActuales = $this->huespedes()->count();

        return $huespedesActuales < $capacidadMaxima;
    }
}