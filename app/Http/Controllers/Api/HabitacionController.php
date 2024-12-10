<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\HabitacionNotFoundException;
use App\Exceptions\HotelNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Habitacion\BulkStoreRequest;
use App\Http\Requests\Habitacion\PutRequest;
use App\Http\Requests\Habitacion\StoreRequest;
use App\Models\Habitacion;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * @OA\Tag(name="Habitación", description="Operaciones relacionadas con las habitaciones")
 */
class HabitacionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/habitaciones",
     *     summary="Obtener lista de habitaciones paginada con filtros dinámicos",
     *     tags={"Habitación"},
     *     @OA\Parameter(
     *         name="numero",
     *         in="query",
     *         description="Número de habitación para filtrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="tipo",
     *         in="query",
     *         description="Tipo de habitación para filtrar (ej: simple, doble)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="precioNoche",
     *         in="query",
     *         description="Precio por noche para filtrar",
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="hotel_id",
     *         in="query",
     *         description="ID del hotel asociado para filtrar",
     *         @OA\Schema(type="integer")
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
     *     @OA\Response(response=200, description="Lista de habitaciones filtrada y paginada")
     * )
     */
    public function index(Request $request)
    {
        $query = Habitacion::query();

        // Filtros dinámicos
        $filterableAttributes = ['numero', 'tipo', 'precioNoche', 'hotel_id'];
        foreach ($request->all() as $key => $value) {
            if (in_array($key, $filterableAttributes) && !empty($value)) {
                $query->where($key, 'like', '%' . $value . '%');
            }
        }

        // Paginación personalizada
        $perPage = $request->query('per_page', 10); // Por defecto 10 elementos por página
        $habitaciones = $query->paginate($perPage);

        if ($habitaciones->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se han encontrado habitaciones con los filtros seleccionados.',
                'codigo' => 404,
            ], 404);
        }

        return response()->json($habitaciones);
    }


    /**
     * @OA\Get(
     *     path="/api/habitaciones/all",
     *     summary="Obtener todas las habitaciones",
     *     tags={"Habitación"},
     *     @OA\Response(response=200, description="Lista completa de habitaciones")
     * )
     */
    public function all()
    {
        return response()->json(Habitacion::get());
    }

    /**
     * @OA\Post(
     *     path="/api/habitaciones",
     *     summary="Crear una nueva habitación",
     *     tags={"Habitación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreHabitacionRequest")
     *     ),
     *     @OA\Response(response=201, description="Habitación creada correctamente"),
     *     @OA\Response(response=404, description="Hotel no encontrado")
     * )
     */
    public function store(StoreRequest $request)
    {
        $hotel = Hotel::find($request->hotel_id);

        if (!$hotel) {
            throw new HotelNotFoundException($request->hotel_id);
        }

        $data = $request->validated();

        $habitacion = new Habitacion($data);
        $habitacion->timestamps = false; // Evita la actualización automática de timestamps
        $habitacion->created_at = now(); // Establece created_at manualmente
        $habitacion->updated_at = null; // No queremos modificar updated_at en creación
        $habitacion->save();

        //$habitacion = Habitacion::create($request->validated());
        return response()->json($habitacion, 201);
    }

    // public function bulkStore(BulkStoreRequest $request)
    // {

    //     // Se extraen los datos validados de las habitaciones
    //     dd($request->all());  // Muestra los datos enviados en la solicitud

    //     $habitacionesData = $request->validated()['habitaciones'];

    //     // Crear las habitaciones en batch
    //     foreach ($habitacionesData as $habitacionData) {
    //         $hotel = Hotel::find($habitacionData['hotel_id']);

    //         if (!$hotel) {
    //             throw new HotelNotFoundException($habitacionData['hotel_id']);
    //         }

    //         $habitacion = new Habitacion($habitacionData);
    //         $habitacion->timestamps = false; // Evita la actualización automática de timestamps
    //         $habitacion->created_at = now(); // Establece created_at manualmente
    //         $habitacion->updated_at = null; // No modificamos updated_at en creación
    //         $habitacion->save();
    //     }

    //     return response()->json([
    //         'message' => 'Habitaciones creadas correctamente',
    //         'data' => $habitacionesData
    //     ], 201);
    // }



    /**
     * @OA\Get(
     *     path="/api/habitaciones/{habitacion}",
     *     summary="Obtener detalles de una habitación",
     *     tags={"Habitación"},
     *     @OA\Parameter(name="habitacion", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalles de la habitación"),
     *     @OA\Response(response=404, description="Habitación no encontrada")
     * )
     */
    public function show($idHabitacion)
    {
        $habitacion = Habitacion::find($idHabitacion);

        if (!$habitacion) {
            throw new HabitacionNotFoundException($idHabitacion);
        }

        return response()->json($habitacion);
    }

    /**
     * @OA\Put(
     *     path="/api/habitaciones/{habitacion}",
     *     summary="Actualizar una habitación",
     *     tags={"Habitación"},
     *     @OA\Parameter(name="habitacion", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PutHabitacionRequest")
     *     ),
     *     @OA\Response(response=200, description="Habitación actualizada correctamente"),
     *     @OA\Response(response=404, description="Habitación no encontrada")
     * )
     *
     *
     * @OA\Patch(
     *     path="/api/habitaciones/{habitacion}",
     *     summary="Actualizar parcial de una habitación",
     *     tags={"Habitación"},
     *     @OA\Parameter(name="habitacion", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PutHabitacionRequest")
     *     ),
     *     @OA\Response(response=200, description="Habitación actualizada correctamente"),
     *     @OA\Response(response=404, description="Habitación no encontrada")
     * )
     */
    public function update(PutRequest $request, $idHabitacion)
    {
        $habitacion = Habitacion::find($idHabitacion);

        if (!$habitacion) {
            throw new HabitacionNotFoundException($idHabitacion);
        }

        $habitacion->update($request->validated());
        return response()->json($habitacion);
    }

    /**
     * @OA\Delete(
     *     path="/api/habitaciones/{habitacion}",
     *     summary="Eliminar una habitación",
     *     tags={"Habitación"},
     *     @OA\Parameter(name="habitacion", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Habitación eliminada correctamente"),
     *     @OA\Response(response=404, description="Habitación no encontrada")
     * )
     */
    public function destroy($idHabitacion)
    {
        $habitacion = Habitacion::find($idHabitacion);

        if (!$habitacion) {
            throw new HabitacionNotFoundException($idHabitacion);
        }

        $habitacion->delete();
        return response()->json(['message' => 'Habitación eliminada correctamente']);
    }

    /**
     * @OA\Get(
     *     path="/api/habitaciones/{habitacion}/hotel",
     *     summary="Obtener el hotel asociado a una habitación",
     *     tags={"Habitación"},
     *     @OA\Parameter(name="habitacion", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Hotel asociado"),
     *     @OA\Response(response=404, description="Habitación no encontrada")
     * )
     */
    public function hotel($idHabitacion)
    {
        $habitacion = Habitacion::find($idHabitacion);

        if (!$habitacion) {
            throw new HabitacionNotFoundException($idHabitacion);
        }

        return response()->json($habitacion->hotel);
    }

    /**
     * @OA\Get(
     *     path="/api/habitaciones/{habitacion}/huespedes",
     *     summary="Obtener los huéspedes de una habitación",
     *     tags={"Habitación"},
     *     @OA\Parameter(name="habitacion", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de huéspedes"),
     *     @OA\Response(response=404, description="Habitación no encontrada o sin huéspedes")
     * )
     */
    public function huespedes($idHabitacion)
    {
        $habitacion = Habitacion::find($idHabitacion);

        if (!$habitacion) {
            throw new HabitacionNotFoundException($idHabitacion);
        }

        $huespedes = $habitacion->huespedes;

        return response()->json($huespedes->isEmpty() ? ['message' => 'Esta habitación no tiene huéspedes'] : $huespedes);
    }
}