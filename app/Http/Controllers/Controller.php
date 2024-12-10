<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;


/**
 * @OA\Info(
 *     title="API Gestion Hoteles",
 *     version="1.1",
 *     description="desciption"
 *     )
 * )
 * 
 * @OA\Server(url="http://localhost:8000/")
 * 
 * @OA\Schema(
 *     schema="Hotel",
 *     type="object",
 *     title="Hotel",
 *     description="Modelo de un hotel",
 *     required={"nombre", "direccion"},
 *     @OA\Property(property="id", type="integer", description="ID del hotel"),
 *     @OA\Property(property="nombre", type="string", description="Nombre del hotel"),
 *     @OA\Property(property="direccion", type="string", description="Dirección del hotel"),
 *     @OA\Property(property="telefono", type="string", description="Teléfono de contacto del hotel"),
 *     @OA\Property(property="email", type="string", format="email", description="Correo electrónico del hotel"),
 *     @OA\Property(property="sitioWeb", type="string", description="Sitio web del hotel"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de última actualización")
 * )
 * 
 * @OA\Schema(
 *     schema="StoreHotelRequest",
 *     type="object",
 *     title="Store Hotel Request",
 *     description="Datos requeridos para crear un nuevo hotel",
 *     required={"nombre", "direccion", "telefono", "email", "sitioWeb"},
 *     @OA\Property(property="nombre", type="string", description="Nombre del hotel (mínimo 5 caracteres, máximo 50)", minLength=5, maxLength=50),
 *     @OA\Property(property="direccion", type="string", description="Dirección del hotel (mínimo 5 caracteres, máximo 100)", minLength=5, maxLength=100),
 *     @OA\Property(property="telefono", type="string", description="Teléfono del hotel (máximo 20 caracteres)", maxLength=20),
 *     @OA\Property(property="email", type="string", format="email", description="Correo electrónico del hotel (mínimo 5 caracteres)", minLength=5),
 *     @OA\Property(property="sitioWeb", type="string", description="Sitio web del hotel (mínimo 5 caracteres)", minLength=5)
 * )
 * 
 * @OA\Schema(
 *     schema="PutHotelRequest",
 *     type="object",
 *     title="Put Hotel Request",
 *     description="Datos requeridos para crear un nuevo hotel",
 *     required={"nombre", "direccion", "telefono", "email", "sitioWeb"},
 *     @OA\Property(property="nombre", type="string", description="Nombre del hotel (mínimo 5 caracteres, máximo 50)", minLength=5, maxLength=50),
 *     @OA\Property(property="direccion", type="string", description="Dirección del hotel (mínimo 5 caracteres, máximo 100)", minLength=5, maxLength=100),
 *     @OA\Property(property="telefono", type="string", description="Teléfono del hotel (máximo 20 caracteres)", maxLength=20),
 *     @OA\Property(property="email", type="string", format="email", description="Correo electrónico del hotel (mínimo 5 caracteres)", minLength=5),
 *     @OA\Property(property="sitioWeb", type="string", description="Sitio web del hotel (mínimo 5 caracteres)", minLength=5)
 * )
 * 
 *  * @OA\Schema(
 *     schema="Habitacion",
 *     type="object",
 *     title="Habitación",
 *     description="Modelo de una habitación",
 *     required={"numero", "tipo", "precioNoche", "hotel_id"},
 *     @OA\Property(property="id", type="integer", description="ID de la habitación"),
 *     @OA\Property(property="numero", type="string", description="Número de la habitación"),
 *     @OA\Property(property="tipo", type="string", description="Tipo de habitación (ej. individual, doble)"),
 *     @OA\Property(property="precioNoche", type="number", format="float", description="Precio por noche de la habitación"),
 *     @OA\Property(property="hotel_id", type="integer", description="ID del hotel al que pertenece la habitación"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de última actualización")
 * )
 * 
 * @OA\Schema(
 *     schema="StoreHabitacionRequest",
 *     type="object",
 *     title="Store Habitación Request",
 *     description="Datos requeridos para crear una nueva habitación",
 *     required={"numero", "tipo", "precioNoche", "hotel_id"},
 *     @OA\Property(property="numero", type="string", description="Número de la habitación", maxLength=10),
 *     @OA\Property(property="tipo", type="string", description="Tipo de habitación", minLength=5, maxLength=20),
 *     @OA\Property(property="precioNoche", type="number", format="float", description="Precio por noche"),
 *     @OA\Property(property="hotel_id", type="integer", description="ID del hotel al que pertenece")
 * )
 * 
 * @OA\Schema(
 *     schema="PutHabitacionRequest",
 *     type="object",
 *     title="Put Habitación Request",
 *     description="Datos requeridos para actualizar una habitación",
 *     required={"numero", "tipo", "precioNoche", "hotel_id"},
 *     @OA\Property(property="numero", type="string", description="Número de la habitación", maxLength=10),
 *     @OA\Property(property="tipo", type="string", description="Tipo de habitación", minLength=5, maxLength=20),
 *     @OA\Property(property="precioNoche", type="number", format="float", description="Precio por noche"),
 *     @OA\Property(property="hotel_id", type="integer", description="ID del hotel al que pertenece")
 * )
 * 
 *  * @OA\Schema(
 *     schema="Huesped",
 *     type="object",
 *     title="Huesped",
 *     description="Modelo de un huésped",
 *     required={"nombre", "apellido", "dniPasaporte"},
 *     @OA\Property(property="id", type="integer", description="ID del huésped"),
 *     @OA\Property(property="nombre", type="string", description="Nombre del huésped"),
 *     @OA\Property(property="apellido", type="string", description="Apellido del huésped"),
 *     @OA\Property(property="dniPasaporte", type="string", description="DNI o pasaporte del huésped"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de última actualización")
 * )
 *
 * @OA\Schema(
 *     schema="StoreHuespedRequest",
 *     type="object",
 *     title="Store Huesped Request",
 *     description="Datos requeridos para crear un nuevo huésped",
 *     required={"nombre", "apellido", "dniPasaporte"},
 *     @OA\Property(property="nombre", type="string", description="Nombre del huésped"),
 *     @OA\Property(property="apellido", type="string", description="Apellido del huésped"),
 *     @OA\Property(property="dniPasaporte", type="string", description="DNI o pasaporte del huésped")
 * )
 *
 * @OA\Schema(
 *     schema="PutHuespedRequest",
 *     type="object",
 *     title="Put Huesped Request",
 *     description="Datos requeridos para actualizar un huésped",
 *     required={"nombre", "apellido", "dniPasaporte"},
 *     @OA\Property(property="nombre", type="string", description="Nombre del huésped"),
 *     @OA\Property(property="apellido", type="string", description="Apellido del huésped"),
 *     @OA\Property(property="dniPasaporte", type="string", description="DNI o pasaporte del huésped")
 * )
 * 
 *  * @OA\Schema(
 *     schema="Servicio",
 *     type="object",
 *     title="Servicio",
 *     description="Modelo de un servicio",
 *     required={"nombre", "descripcion", "categoria"},
 *     @OA\Property(property="id", type="integer", description="ID del servicio"),
 *     @OA\Property(property="nombre", type="string", description="Nombre del servicio"),
 *     @OA\Property(property="descripcion", type="string", description="Descripción del servicio"),
 *     @OA\Property(property="categoria", type="string", description="Categoría del servicio"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de última actualización")
 * )
 *
 * @OA\Schema(
 *     schema="StoreServicioRequest",
 *     type="object",
 *     title="Store Servicio Request",
 *     description="Datos requeridos para crear un nuevo servicio",
 *     required={"nombre", "descripcion", "categoria"},
 *     @OA\Property(property="nombre", type="string", description="Nombre del servicio"),
 *     @OA\Property(property="descripcion", type="string", description="Descripción del servicio"),
 *     @OA\Property(property="categoria", type="string", description="Categoría del servicio")
 * )
 *
 * @OA\Schema(
 *     schema="PutServicioRequest",
 *     type="object",
 *     title="Put Servicio Request",
 *     description="Datos requeridos para actualizar un servicio",
 *     required={"nombre", "descripcion", "categoria"},
 *     @OA\Property(property="nombre", type="string", description="Nombre del servicio"),
 *     @OA\Property(property="descripcion", type="string", description="Descripción del servicio"),
 *     @OA\Property(property="categoria", type="string", description="Categoría del servicio")
 * )
 * 
 * 
 * 
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
