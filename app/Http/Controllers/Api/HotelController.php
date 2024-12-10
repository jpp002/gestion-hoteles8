<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\HotelNotFoundException;
use App\Exceptions\ServicioNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hotel\PutRequest;
use App\Http\Requests\Hotel\StoreCascadaRequest;
use App\Http\Requests\Hotel\StoreRequest;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
 * 
 * @OA\Tag(
 *     name="Hotel",
 *     description="Operaciones relacionadas con los hoteles"
 * )
 */

class HotelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/hotel",
     *     summary="Obtener lista de hoteles paginada con filtros dinámicos",
     *     tags={"Hotel"},
     *     @OA\Parameter(
     *         name="nombre",
     *         in="query",
     *         description="Nombre del hotel para filtrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="direccion",
     *         in="query",
     *         description="Dirección del hotel para filtrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="telefono",
     *         in="query",
     *         description="Teléfono del hotel para filtrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Email del hotel para filtrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sitioWeb",
     *         in="query",
     *         description="Sitio web del hotel para filtrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de elementos por página",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(
     *         name="includeHabitaciones",
     *         in="query",
     *         description="Inluye el numero de habitaciones",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(response=200, description="Lista de hoteles filtrada y paginada")
     * )
     */

    public function index(Request $request)
    {
        $query = Hotel::query();

        // Aplicar filtros dinámicos basados en los parámetros de consulta
        $filterableAttributes = ['nombre', 'direccion', 'telefono', 'email', 'sitioWeb'];
        foreach ($request->all() as $key => $value) {
            if (in_array($key, $filterableAttributes) && !empty($value)) {
                $query->where($key, 'like', '%' . $value . '%');
            }
        }

        // Verificar si se debe incluir la relación 'habitaciones'
        if ($request->query('includeHabitaciones') === 'true') {
            $query->with('habitaciones');
        }

        if ($request->query('includeServicios') === 'true') {
            $query->with('servicios');
        }

        // Manejo de paginación personalizada
        $perPage = $request->query('per_page', 10); // Por defecto 10 elementos por página
        $hoteles = $query->paginate($perPage);

        if ($hoteles->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se han encontrado hoteles con los filtros seleccionados.',
                'codigo' => 404,
            ], 404);
        }

        return response()->json($hoteles);
    }




    /**
     * @OA\Get(
     *     path="/api/hotel/all",
     *     summary="Obtener todos los hoteles",
     *     tags={"Hotel"},
     *     @OA\Response(response=200, description="Lista de todos los hoteles")
     * )
     */
    public function all()
    {
        return response()->json(Hotel::get());
    }

    /**
     * @OA\Post(
     *     path="/api/hotel",
     *     summary="Crear un nuevo hotel",
     *     tags={"Hotel"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreHotelRequest")),
     *     @OA\Response(response=201, description="Hotel creado correctamente")
     * )
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        // Crear el hotel manualmente
        $hotel = new Hotel($data);
        $hotel->timestamps = false; 
        $hotel->created_at = now(); 
        $hotel->updated_at = null; 
        $hotel->save();

        return response()->json($hotel, 201);
    }


    /**
     * @OA\Get(
     *     path="/api/hotel/{hotel}",
     *     summary="Obtener detalles de un hotel",
     *     tags={"Hotel"},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(
     *         name="includeHabitaciones",
     *         in="query",
     *         description="Inluye las habitaciones del hotel",
     *         @OA\Schema(type="boolean")
     *     ),
     * @OA\Parameter(
     *         name="includeServicios",
     *         in="query",
     *         description="Inluye los servicios del hotel",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(response=200, description="Detalles del hotel"),
     *     @OA\Response(response=404, description="Hotel no encontrado")
     * )
     */
    public function show(Request $request, $idHotel)
    {
        // Verificar si se debe incluir la relación 'habitaciones'
        $includeHabitaciones = $request->query('includeHabitaciones') === 'true';

        // Obtener el hotel con o sin habitaciones, según el parámetro
        $query = Hotel::query();
        if ($includeHabitaciones) {
            $query->with('habitaciones');
        }
        if ($request->query('includeServicios') === 'true') {
            $query->with('servicios');
        }

        $hotel = $query->find($idHotel);

        if (!$hotel) {
            throw new HotelNotFoundException($idHotel);
        }

        return response()->json($hotel, 200);
    }



    /**
     * @OA\Put(
     *     path="/api/hotel/{hotel}",
     *     summary="Actualizar un hotel",
     *     tags={"Hotel"},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PutHotelRequest")),
     *     @OA\Response(response=200, description="Hotel actualizado correctamente"),
     *     @OA\Response(response=404, description="Hotel no encontrado")
     * )
     * @OA\Patch(
     *     path="/api/hotel/{hotel}",
     *     summary="Actualizar parcialmente un hotel",
     *     tags={"Hotel"},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PutHotelRequest")),
     *     @OA\Response(response=200, description="Hotel actualizado correctamente"),
     *     @OA\Response(response=404, description="Hotel no encontrado")
     * )
     */
    public function update(PutRequest $request,  $idHotel)
    {
        $hotel = Hotel::find($idHotel);


        if (!$hotel) {
            throw new HotelNotFoundException($idHotel);
        }

        $data = $request->validated();

        $hotel->touch();
        $hotel->update($data);
        return response()->json($hotel);
    }

    /**
     * @OA\Delete(
     *     path="/api/hotel/{hotel}",
     *     summary="Eliminar un hotel",
     *     tags={"Hotel"},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Hotel eliminado correctamente"),
     *     @OA\Response(response=404, description="Hotel no encontrado")
     * )
     */
    public function destroy($idHotel)
    {
        $hotel = Hotel::find($idHotel);
        if (!$hotel) {
            throw new HotelNotFoundException($idHotel);
        }

        try {
            $hotel->delete();
            return response()->json(["message" => "Hotel eliminado correctamente"], 200);
        } catch (\Exception $e) {
            return response()->json(["error" => "No se pudo eliminar el hotel"], 400);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/hotel/{hotel}/habitaciones",
     *     summary="Obtener las habitaciones de un hotel",
     *     tags={"Hotel"},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de habitaciones")
     * )
     */
    // public function habitaciones($idHotel)
    // {
    //     $hotel = Hotel::find($idHotel);
    //     if (!$hotel) {
    //         throw new HotelNotFoundException($idHotel);
    //     }

    //     $habitaciones = $hotel->habitaciones;

    //     if ($habitaciones->isEmpty()) {
    //         return response()->json(["message" => "Este hotel no tiene habitaciones"], 404);
    //     }

    //     return response()->json($habitaciones, 200);
    // }

    /**
     * @OA\Post(
     *     path="/api/hotel/{hotel}/servicio/{servicio}",
     *     summary="Asociar un servicio a un hotel",
     *     tags={"Hotel"},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="servicio", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Servicio asociado correctamente"),
     *     @OA\Response(response=400, description="Error en la asociación")
     * )
     */
    // public function addServicio($idHotel, $idServicio)
    // {
    //     $hotel = Hotel::find($idHotel);
    //     if (!$hotel) {
    //         throw new HotelNotFoundException($idHotel);
    //     }

    //     $servicio = servicio::find($idServicio);
    //     if (!$servicio) {
    //         throw new ServicioNotFoundException($idServicio);
    //     }

    //     if ($hotel->servicios->contains($servicio->id)) {
    //         return response()->json(['message' => 'El servicio ya está asociado a este hotel'], 400);
    //     }

    //     $hotel->servicios()->attach($servicio->id);

    //     return response()->json(['message' => 'Servicio asociado correctamente'], 200);
    // }



    /**
     * @OA\Delete(
     *     path="/api/hotel/{hotel}/servicio/{servicio}",
     *     summary="Desasociar un servicio de un hotel",
     *     tags={"Hotel"},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="servicio", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Servicio desasociado correctamente"),
     *     @OA\Response(response=400, description="Error en la desasociación")
     * )
     */
    // public function removeServicio($idHotel, $idServicio)
    // {
    //     $hotel = Hotel::find($idHotel);
    //     if (!$hotel) {
    //         throw new HotelNotFoundException($idHotel);
    //     }

    //     $servicio = servicio::find($idServicio);
    //     if (!$servicio) {
    //         throw new ServicioNotFoundException($idServicio);
    //     }


    //     if (!$hotel->servicios->contains($servicio->id)) {
    //         return response()->json(['message' => 'El servicio no está asociado a este hotel'], 400);
    //     }

    //     $hotel->servicios()->detach($servicio->id);

    //     return response()->json(['message' => 'Servicio desasociado correctamente'], 200);
    // }

    /**
     * @OA\Get(
     *     path="/api/hotel/{hotel}/servicios",
     *     summary="Obtener los servicios de un hotel",
     *     tags={"Hotel"},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de servicios")
     * )
     */
    // public function servicios($idHotel)
    // {
    //     $hotel = Hotel::find($idHotel);
    //     if (!$hotel) {
    //         throw new HotelNotFoundException($idHotel);
    //     }

    //     $servicios = $hotel->servicios;

    //     if ($servicios->isEmpty()) {
    //         return response()->json(["message" => "Este hotel no tiene servicios"], 404);
    //     }
    //     return response()->json($servicios, 200);
    // }

    /**
     * @OA\Post(
     *     path="/api/hotel/cascada",
     *     summary="Crear un hotel con habitaciones y servicios en cascada",
     *     tags={"Hotel"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nombre", type="string", example="Hotel Ejemplo"),
     *             @OA\Property(property="direccion", type="string", example="Calle Falsa 123"),
     *             @OA\Property(property="telefono", type="string", example="123456789"),
     *             @OA\Property(property="email", type="string", example="hotel@ejemplo.com"),
     *             @OA\Property(property="sitioWeb", type="string", example="http://hotel-ejemplo.com"),
     *             @OA\Property(
     *                 property="habitaciones",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="numero", type="string", example="101"),
     *                     @OA\Property(property="tipo", type="string", example="Deluxe"),
     *                     @OA\Property(property="precioNoche", type="number", format="float", example=100.50)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="servicios",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="nombre", type="string", example="WiFi"),
     *                     @OA\Property(property="descripcion", type="string", example="Internet de alta velocidad")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Hotel creado con éxito junto con sus relaciones"),
     *     @OA\Response(response=400, description="Error en los datos enviados")
     * )
     */
    /*
    public function cascada(StoreCascadaRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Crear el hotel
            $hotel = Hotel::create([
                'nombre' => $data['nombre'],
                'direccion' => $data['direccion'],
                'telefono' => $data['telefono'],
                'email' => $data['email'],
                'sitioWeb' => $data['sitioWeb'] ?? null,
            ]);
            

            // Crear habitaciones
            if (isset($data['habitaciones'])) {
                foreach ($data['habitaciones'] as $habitacionData) {
                    $habitacion = new Habitacion($habitacionData);
                    $hotel->habitaciones()->save($habitacion);
                }
            }

            // Crear servicios
            if (isset($data['servicios'])) {
                foreach ($data['servicios'] as $servicioData) {
                    $servicio = Servicio::firstOrCreate(
                        ['nombre' => $servicioData['nombre']],
                        ['descripcion' => $servicioData['descripcion'] ?? null]
                    );
                    $hotel->servicios()->attach($servicio->id);
                }
            }

            DB::commit();
            
            $request2 = new \Illuminate\Http\Request();
            $request2->merge(['includeHabitaciones' => 'true', 'includeServicios' => 'true']);
            return $this->show($request2, $hotel->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear el hotel: ' . $e->getMessage()], 400);
        }
    }*/
}