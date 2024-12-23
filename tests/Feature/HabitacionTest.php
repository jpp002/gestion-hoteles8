<?php

namespace Tests\Feature;

use App\Models\Habitacion;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HabitacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un hotel inicial para todos los tests
        Hotel::factory()->create();
    }


    /**
     * Test: Listar habitaciones con éxito.
     */
    public function test_puede_listar_habitaciones(): void
    {
        $hotel = Hotel::factory()->create();
        Habitacion::factory()->count(3)->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson('/api/habitaciones');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Test: Crear una habitación con éxito.
     */
    public function test_puede_crear_habitacion(): void
    {
        $hotel = Hotel::factory()->create();

        $data = [
            'numero' => '101',
            'tipo' => 'simple',
            'precioNoche' => 100.00,
            'hotel_id' => $hotel->id,
        ];

        $response = $this->postJson('/api/habitaciones', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment(['numero' => '101']);
        $this->assertDatabaseHas('habitaciones', $data);
    }

    /**
     * Test: Obtener detalles de una habitación existente.
     */
    public function test_puede_mostar_una_habitacion(): void
    {
        $habitacion = Habitacion::factory()->create([
            'numero' => '233',
            'tipo' => 'simple',
            'precioNoche' => 100.00,
        ]);

        $response = $this->getJson("/api/habitaciones/{$habitacion->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $habitacion->id, 'numero' => '233']);
    }


    /**
     * Test: Actualizar una habitación existente.
     */
    public function test_puede_actualizar_una_habitacion(): void
    {
        $habitacion = Habitacion::factory()->create();
        $hotel = Hotel::factory()->create();

        $payload = ['numero' => '2', 'tipo' => 'doble', 'precioNoche' => 200.00, 'hotel_id' => $hotel->id];

        $response = $this->putJson("/api/habitaciones/{$habitacion->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('habitaciones', array_merge(['id' => $habitacion->id], $payload));
    }

    /**
     * Test: Eliminar una habitación con éxito.
     */
    public function test_puede_borrar_una_habitacion(): void
    {
        $habitacion = Habitacion::factory()->create();

        $response = $this->deleteJson("/api/habitaciones/{$habitacion->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('habitaciones', ['id' => $habitacion->id]);
    }

    /**
     * Test: Obtener el hotel de una habitación.
     */
    public function test_puede_obtener_hotel_de_habitacion(): void
    {
        $hotel = Hotel::factory()->create();
        $habitacion = Habitacion::factory()->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson("/api/habitaciones/{$habitacion->id}/hotel");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $hotel->id]);
    }

    /**
     * Test: Manejo de error al consultar una habitación inexistente.
     */
    public function test_no_existe_habitacion(): void
    {
        $response = $this->getJson('/api/habitaciones/999');

        $response->assertStatus(404)
            ->assertJsonFragment(['Error' => 'La habitacion con ID 999 no existe.']);
    }

    /**
     * Test: Manejo de error al crear una habitación con hotel inexistente.
     */
    public function test_crear_habitacion_no_existe_hotel(): void
    {
        $payload = [
            'numero' => '101',
            'tipo' => 'simple',
            'precioNoche' => 100.00,
            'hotel_id' => 999, // Hotel inexistente
        ];

        $response = $this->postJson('/api/habitaciones', $payload);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'El hotel con ID 999 no existe.']);
    }
}
