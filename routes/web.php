<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('vue');
});

Route::get('/login', function () {
    return 'Página de login no implementada';
})->name('login');


Route::get('/setup', function () {
    $credentials = [
        'email' => "user@user.com",
        'password' => "12345678"
        
    ];

    if (!Auth::attempt($credentials)) {
        $user = new User();
        $user->name = 'Usuario';
        $user->email = $credentials['email'];
        $user->password = Hash::make($credentials['password']);
        $user->save();
    }
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $adminToken = $user->createToken('admin-token', ['all']);
        $updateToken = $user->createToken('update-token', ['not']);
        $basicToken = $user->createToken('basic-token', ['create', 'update']);

        return [
            'admin' => $adminToken->plainTextToken,
            'update' => $updateToken->plainTextToken,
            'basic' => $basicToken->plainTextToken,
        ];
    }
});