<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ServicioNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Servicio\PutRequest;
use App\Http\Requests\Servicio\StoreRequest;
use App\Models\Servicio;
use Illuminate\Http\Request;

/**
 *
 * @OA\Tag(
 *     name="Servicio",
 *     description="Operaciones relacionadas con los servicios"
 * )
 */

class ServicioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/servicios",
     *     summary="Obtener lista de servicios paginada con filtros dinámicos",
     *     tags={"Servicio"},
     *     @OA\Parameter(
     *         name="nombre",
     *         in="query",
     *         description="Nombre del servicio para filtrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="descripcion",
     *         in="query",
     *         description="Descripción del servicio para filtrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="categoria",
     *         in="query",
     *         description="Categoría del servicio para filtrar",
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
     *     @OA\Response(response=200, description="Lista de servicios filtrada y paginada")
     * )
     */
    public function index(Request $request)
    {
        $query = Servicio::query();

        // Filtros dinámicos
        $filterableAttributes = ['nombre', 'descripcion', 'categoria'];
        foreach ($request->all() as $key => $value) {
            if (in_array($key, $filterableAttributes) && !empty($value)) {
                $query->where($key, 'like', '%' . $value . '%');
            }
        }

        // Verificar si se debe incluir la relación 'habitaciones'
        if ($request->query('includeHoteles') === 'true') {
            $query->with('hoteles');
        }

        // Paginación personalizada
        $perPage = $request->query('per_page', 10); // Por defecto 10 elementos por página
        $servicios = $query->paginate($perPage);

        if ($servicios->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se han encontrado servicios con los filtros seleccionados.',
                'codigo' => 404,
            ], 404);
        }

        return response()->json($servicios);
    }


    /**
     * @OA\Get(
     *     path="/api/servicios/all",
     *     summary="Obtener todos los servicios",
     *     tags={"Servicio"},
     *     @OA\Response(response=200, description="Lista completa de servicios")
     * )
     */
    public function all()
    {
        return response()->json(Servicio::get());
    }

    /**
     * @OA\Post(
     *     path="/api/servicios",
     *     summary="Crear un nuevo servicio",
     *     tags={"Servicio"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreServicioRequest")
     *     ),
     *     @OA\Response(response=201, description="Servicio creado correctamente"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        $servicio = new Servicio($data);
        $servicio->timestamps = false;
        $servicio->created_at = now();
        $servicio->updated_at = null;
        $servicio->save();

        return response()->json($servicio, 201);
        //return response()->json(Servicio::create($request->validated()));
    }

    /**
     * @OA\Get(
     *     path="/api/servicios/{id}",
     *     summary="Obtener un servicio por ID",
     *     tags={"Servicio"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del servicio", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalles del servicio"),
     *     @OA\Response(response=404, description="Servicio no encontrado")
     * )
     */
    public function show(Request $request, $idServicio)
    {
        $includeHoteles = $request->query('includeHoteles') === 'true';

        $query = Servicio::query();

        if ($includeHoteles) {
            $query->with('hoteles');
        }


        $servicio = $query->find($idServicio);

        if (!$servicio) {
            throw new ServicioNotFoundException($idServicio);
        }



        return response()->json($servicio, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/servicios/{id}",
     *     summary="Actualizar un servicio",
     *     tags={"Servicio"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del servicio", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PutServicioRequest")
     *     ),
     *     @OA\Response(response=200, description="Servicio actualizado correctamente"),
     *     @OA\Response(response=404, description="Servicio no encontrado")
     * )
     * @OA\Patch(
     *     path="/api/servicios/{id}",
     *     summary="Actualizar un servicio",
     *     tags={"Servicio"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del servicio", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PutServicioRequest")
     *     ),
     *     @OA\Response(response=200, description="Servicio actualizado correctamente"),
     *     @OA\Response(response=404, description="Servicio no encontrado")
     * )
     *
     */
    public function update(PutRequest $request, $idServicio)
    {
        $servicio = Servicio::find($idServicio);

        if (!$servicio) {
            throw new ServicioNotFoundException($idServicio);
        }
        $servicio->touch();
        $servicio->update($request->validated());
        return response()->json($servicio);
    }

    /**
     * @OA\Delete(
     *     path="/api/servicios/{id}",
     *     summary="Eliminar un servicio",
     *     tags={"Servicio"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del servicio", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Servicio eliminado"),
     *     @OA\Response(response=404, description="Servicio no encontrado")
     * )
     */
    public function destroy($idServicio)
    {
        $servicio = Servicio::find($idServicio);

        if (!$servicio) {
            throw new ServicioNotFoundException($idServicio);
        }

        $servicio->delete();
        return response()->json("ok");
    }

    /**
     * @OA\Get(
     *     path="/api/servicios/{id}/hoteles",
     *     summary="Obtener hoteles asociados a un servicio",
     *     tags={"Servicio"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del servicio", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de hoteles asociados"),
     *     @OA\Response(response=404, description="Servicio no encontrado")
     * )
     */
    public function hoteles($idServicio)
    {
        $servicio = Servicio::find($idServicio);

        if (!$servicio) {
            throw new ServicioNotFoundException($idServicio);
        }

        $hoteles = $servicio->hoteles;
        if ($hoteles->isEmpty()) {
            return response()->json(["message" => "Este servicio no esta asociado a ningun hotel"], 404);
        }

        return response()->json($hoteles);
    }
}