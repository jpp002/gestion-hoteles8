<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Usuario", description="Operaciones relacionadas con los usuarios")
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/user/login",
     *     summary="Iniciar sesión de usuario",
     *     description="Permite a un usuario iniciar sesión con sus credenciales (email y contraseña). Si las credenciales son correctas, se devuelve un token de autenticación.",
     *     tags={"Usuario"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", description="Correo electrónico del usuario"),
     *             @OA\Property(property="password", type="string", format="password", description="Contraseña del usuario")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso, se devuelve el token de autenticación.",
     *         @OA\JsonContent(
     *             type="string",
     *             example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjM0NTY3ODkwLCJpYXQiOjE1MTYyMzkwMjJ9.dXnp_wHjoOFlvOzx4f56zuwFOjwKBGjvv3wbFsQdSuM"
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas (usuario o contraseña inválidos)",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Usuario y/o contraseña invalida"
     *         )
     *     )
     * )
     */

    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {
            $token = Auth::user()->createToken('myapptoken')->plainTextToken;
            return response()->json($token);
        }
        return response()->json("Usuario y/o contraseña invalida");
    }
}