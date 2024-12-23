<?php

namespace Tests\Feature;

use App\Models\Servicio;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicioTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_lista_de_servicios_paginada()
    {
        Servicio::factory()->count(15)->create();

        $response = $this->getJson('/api/servicios?per_page=10');

        $response->assertStatus(200);
    }

    public function test_obtener_todos_los_servicios()
    {
        Servicio::factory()->count(5)->create();

        $response = $this->getJson('/api/servicios/all');

        $response->assertStatus(200)
                 ->assertJsonCount(5);
    }

    public function test_crear_servicio()
    {
        $data = [
            'nombre' => 'Servicio de Prueba',
            'descripcion' => 'Descripción del servicio de prueba',
            'categoria' => 'Prueba',
        ];

        $response = $this->postJson('/api/servicios', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment($data);

        $this->assertDatabaseHas('servicios', $data);
    }

    public function test_obtener_servicio_por_id()
    {
        $servicio = Servicio::factory()->create();

        $response = $this->getJson("/api/servicios/{$servicio->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $servicio->id,
                     'nombre' => $servicio->nombre,
                 ]);
    }

    public function test_obtener_servicio_por_id_no_existente()
    {
        $response = $this->getJson('/api/servicios/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'Error' => 'El servicio con ID 999 no existe.',
                 ]);
    }

    public function test_actualizar_servicio()
    {
        $servicio = Servicio::factory()->create();

        $data = [
            'nombre' => 'Nombre Actualizado',
            'descripcion' => 'Descripción Actualizada',
            'categoria' => 'Nueva Categoría',
        ];

        $response = $this->putJson("/api/servicios/{$servicio->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment($data);

        $this->assertDatabaseHas('servicios', $data);
    }

    public function test_eliminar_servicio()
    {
        $servicio = Servicio::factory()->create();

        $response = $this->deleteJson("/api/servicios/{$servicio->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('servicios', ['id' => $servicio->id]);
    }

    public function test_obtener_hoteles_asociados_a_un_servicio()
    {
        $servicio = Servicio::factory()->create();
        $hoteles = Hotel::factory()->count(3)->create();
        $servicio->hoteles()->attach($hoteles->pluck('id'));

        $response = $this->getJson("/api/servicios/{$servicio->id}/hoteles");

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_obtener_hoteles_asociados_a_un_servicio_sin_relaciones()
    {
        $servicio = Servicio::factory()->create();

        $response = $this->getJson("/api/servicios/{$servicio->id}/hoteles");

        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'Este servicio no esta asociado a ningun hotel',
                 ]);
    }
}