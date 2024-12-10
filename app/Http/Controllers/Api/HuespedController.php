<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\HabitacionNotFoundException;
use App\Exceptions\HuespedNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Huesped\PutRequest;
use App\Http\Requests\Huesped\StoreRequest;
use App\Models\Huesped;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Tag(name="Huesped", description="Operaciones relacionadas con los huéspedes")
 */
class HuespedController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/huespedes",
 *     summary="Obtener lista de huéspedes paginada con filtros dinámicos",
 *     tags={"Huesped"},
 *     @OA\Parameter(
 *         name="nombre",
 *         in="query",
 *         description="Nombre del huésped para filtrar",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="apellido",
 *         in="query",
 *         description="Apellido del huésped para filtrar",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="dniPasaporte",
 *         in="query",
 *         description="DNI o Pasaporte del huésped para filtrar",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="fechaCheckin",
 *         in="query",
 *         description="Fecha de check-in para filtrar",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="fechaCheckout",
 *         in="query",
 *         description="Fecha de check-out para filtrar",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="habitacion_id",
 *         in="query",
 *         description="ID de la habitación asociada para filtrar",
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
 *     @OA\Response(response=200, description="Lista de huéspedes filtrada y paginada")
 * )
 */
public function index(Request $request)
{
    $query = Huesped::query();

    // Filtros dinámicos
    $filterableAttributes = ['nombre', 'apellido', 'dniPasaporte', 'fechaCheckin', 'fechaCheckout', 'habitacion_id'];
    foreach ($request->all() as $key => $value) {
        if (in_array($key, $filterableAttributes) && !empty($value)) {
            $query->where($key, 'like', '%' . $value . '%');
        }
    }

    // Paginación personalizada
    $perPage = $request->query('per_page', 10); // Por defecto 10 elementos por página
    $huespedes = $query->paginate($perPage);

    if ($huespedes->isEmpty()) {
        return response()->json([
            'mensaje' => 'No se han encontrado huespedes con los filtros seleccionados.',
            'codigo' => 404,
        ], 404);
    }

    return response()->json($huespedes);
}


    /**
     * @OA\Get(
     *     path="/api/huespedes/all",
     *     summary="Obtener todos los huéspedes",
     *     tags={"Huesped"},
     *     @OA\Response(response=200, description="Lista completa de huéspedes")
     * )
     */
    public function all()
    {
        // return response()->json((Cache::remember('huespedes_all', now()->addMinutes(10), function(){
        //     return Huesped::all();
        // })));
         
        return response()->json(Huesped::get());
    }

    /**
     * @OA\Post(
     *     path="/api/huespedes",
     *     summary="Crear un nuevo huésped",
     *     tags={"Huesped"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreHuespedRequest")
     *     ),
     *     @OA\Response(response=201, description="Huésped creado correctamente"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(StoreRequest $request)
    {

        $data = $request->validated();

        $huesped = new Huesped($data);
        $huesped->timestamps = false; 
        $huesped->created_at = now(); 
        $huesped->updated_at = null; 
        $huesped->save();
        
        //$huesped = Huesped::create($request->validated());
        return response()->json($huesped, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/huespedes/{idHuesped}",
     *     summary="Obtener un huésped por ID",
     *     tags={"Huesped"},
     *     @OA\Parameter(name="idHuesped", in="path", required=true, description="ID del huésped", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalles del huésped"),
     *     @OA\Response(response=404, description="Huésped no encontrado")
     * )
     */
    public function show($idHuesped)
    {
        $huesped = Huesped::find($idHuesped);
        if (!$huesped) {
            throw new HuespedNotFoundException($idHuesped);
        }
        return response()->json($huesped);
    }

    /**
     * @OA\Put(
     *     path="/api/huespedes/{idHuesped}",
     *     summary="Actualizar un huésped",
     *     tags={"Huesped"},
     *     @OA\Parameter(name="idHuesped", in="path", required=true, description="ID del huésped", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PutHuespedRequest")
     *     ),
     *     @OA\Response(response=200, description="Huésped actualizado correctamente"),
     *     @OA\Response(response=404, description="Huésped no encontrado")
     * )
     *
     *
     * @OA\Patch(
     *     path="/api/huespedes/{idHuesped}",
     *     summary="Actualizar un huésped",
     *     tags={"Huesped"},
     *     @OA\Parameter(name="idHuesped", in="path", required=true, description="ID del huésped", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PutHuespedRequest")
     *     ),
     *     @OA\Response(response=200, description="Huésped actualizado correctamente"),
     *     @OA\Response(response=404, description="Huésped no encontrado")
     * )
     */
    public function update(PutRequest $request, $idHuesped)
    {
        $huesped = Huesped::find($idHuesped);
        if (!$huesped) {
            throw new HuespedNotFoundException($idHuesped);
        }

        $huesped->touch();
        $huesped->update($request->validated()); 
        return response()->json($huesped);
    }

    /**
     * @OA\Delete(
     *     path="/api/huespedes/{idHuesped}",
     *     summary="Eliminar un huésped",
     *     tags={"Huesped"},
     *     @OA\Parameter(name="idHuesped", in="path", required=true, description="ID del huésped", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Huésped eliminado"),
     *     @OA\Response(response=404, description="Huésped no encontrado")
     * )
     */
    public function destroy($idHuesped)
    {
        $huesped = Huesped::find($idHuesped);
        if (!$huesped) {
            throw new HuespedNotFoundException($idHuesped);
        }
        $huesped->delete();
        return response()->json(['message' => 'Huésped eliminado correctamente']);
    }

    /**
     * @OA\Get(
     *     path="/api/huespedes/{idHuesped}/habitacion",
     *     summary="Obtener la habitación de un huésped",
     *     tags={"Huesped"},
     *     @OA\Parameter(name="idHuesped", in="path", required=true, description="ID del huésped", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Habitación del huésped"),
     *     @OA\Response(response=404, description="Huésped no encontrado")
     * )
     */
    public function habitacion($idHuesped)
    {
        $huesped = Huesped::find($idHuesped);
        if (!$huesped) {
            throw new HuespedNotFoundException($idHuesped);
        }
        return response()->json($huesped->habitacion);
    }

    /**
     * @OA\Post(
     *     path="/api/huespedes/{idHuesped}/reservar/{idHabitacion}",
     *     summary="Reservar una habitación para un huésped",
     *     tags={"Huesped"},
     *     @OA\Parameter(name="idHuesped", in="path", required=true, description="ID del huésped", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="idHabitacion", in="path", required=true, description="ID de la habitación", @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Habitación reservada correctamente"),
     *     @OA\Response(response=400, description="Habitación no disponible"),
     *     @OA\Response(response=404, description="Huésped o habitación no encontrado")
     * )
     */
    public function reservarHabitacion($idHuesped, $idHabitacion)
    {
        $huesped = Huesped::find($idHuesped);
        $habitacion = Habitacion::find($idHabitacion);

        if (!$huesped ) {
            throw new HuespedNotFoundException($huesped);
        }
        elseif (!$habitacion) {
            throw new HabitacionNotFoundException($habitacion);
        }

        if (!$habitacion->isDisponible()) {
            return response()->json(['message' => "La habitación no está disponible."], 400);
        }

        $huesped->habitacion()->associate($habitacion)->save();
        
        $huesped->fechaCheckin = now();  
        $huesped->save();
        return response()->json($huesped, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/huespedes/{idHuesped}/checkout",
     *     summary="Registrar el check-out del huésped y liberar la habitación",
     *     tags={"Huesped"},
     *     @OA\Parameter(name="idHuesped", in="path", required=true, description="ID del huésped", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Check-out registrado correctamente"),
     *     @OA\Response(response=404, description="Huésped no encontrado")
     * )
     */
    public function checkoutHabitacion($idHuesped)
    {
        $huesped = Huesped::find($idHuesped);

        if (!$huesped) {
            throw new HuespedNotFoundException($idHuesped);
        }

        $huesped->fechaCheckout = now();
        $huesped->save();

        if (!$huesped->habitacion){
            return response()->json(['message' => "El huesped $huesped->nombre no tiene reservada ninguna habitación."], 200);
        }

        $huesped->habitacion()->dissociate();
        $huesped->save();

        return response()->json(['message' => "Check-out registrado correctamente y habitación liberada."], 200);
    }
}