<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Servicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test para listar hoteles.
     */
    public function test_puede_listar_hoteles(): void
    {
        Hotel::factory()->count(3)->create();

        $response = $this->getJson('/api/hoteles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                //'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    /**
     * Test para listar hoteles con filtros.
     */
    public function test_puede_filtrar_hoteles_por_nombre(): void
    {
        Hotel::factory()->create(['nombre' => 'Hotel Prueba']);
        Hotel::factory()->create(['nombre' => 'Otro Hotel']);

        $response = $this->getJson('/api/hoteles?nombre=Prueba');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['nombre' => 'Hotel Prueba']);
    }

    /**
     * Test para mostrar detalles de un hotel existente.
     */
    public function test_puede_mostrar_un_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->getJson("/api/hoteles/{$hotel->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $hotel->id, 'nombre' => $hotel->nombre]);
    }

    /**
     * Test para manejar un hotel no encontrado.
     */
    public function test_retorna_error_si_el_hotel_no_existe(): void
    {
        $response = $this->getJson('/api/hoteles/999');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'El hotel con ID 999 no existe.']);
    }

    /**
     * Test para crear un hotel.
     */
    public function test_puede_crear_un_hotel(): void
    {
        $data = [
            'nombre' => 'Nuevo Hotel',
            'direccion' => 'Calle Falsa 123',
            'telefono' => '123456789',
            'email' => 'hotel@example.com',
            'sitioWeb' => 'https://hotel.com',
        ];

        $response = $this->postJson('/api/hoteles', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['nombre' => 'Nuevo Hotel']);
        $this->assertDatabaseHas('hoteles', $data);
    }

    /**
     * Test para actualizar un hotel.
     */
    public function test_puede_actualizar_un_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $data = [
            'nombre' => 'Hotel Actualizado',
            'direccion' => 'Nueva dirección',
            'telefono' => '123456789',
            'email' => 'nuevoemail@hotel.com',
            'sitioWeb' => 'https://nuevohotel.com',
        ];

        $response = $this->putJson("/api/hoteles/{$hotel->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['nombre' => 'Hotel Actualizado']);
        $this->assertDatabaseHas('hoteles', $data);
    }


    /**
     * Test para eliminar un hotel.
     */
    public function test_puede_eliminar_un_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->deleteJson("/api/hoteles/{$hotel->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Hotel eliminado correctamente']);
        $this->assertDatabaseMissing('hoteles', ['id' => $hotel->id]);
    }

    /**
     * Test para asociar un servicio a un hotel.
     */
    public function test_puede_asociar_servicio_a_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $servicio = Servicio::factory()->create();

        $response = $this->postJson("/api/hoteles/{$hotel->id}/servicio/{$servicio->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Servicio asociado correctamente']);
        $this->assertTrue($hotel->servicios->contains($servicio));
    }

    /**
     * Test para listar habitaciones de un hotel.
     */
    public function test_puede_listar_habitaciones_de_un_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $habitacion = $hotel->habitaciones()->create([
            'numero' => '101',
            'tipo' => 'Doble',
            'precioNoche' => 120.50,
        ]);

        $response = $this->getJson("/api/hoteles/{$hotel->id}/habitaciones");

        $response->assertStatus(200)
                 ->assertJsonFragment(['numero' => $habitacion->numero]);
    }

    /**
     * Test para manejar error al listar habitaciones de un hotel sin habitaciones.
     */
    public function test_error_si_hotel_no_tiene_habitaciones(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->getJson("/api/hoteles/{$hotel->id}/habitaciones");

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Este hotel no tiene habitaciones']);
    }
}