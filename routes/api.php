<?php

use App\Http\Controllers\ramos\autos\Equidad\EquidadController;
use App\Http\Controllers\ramos\salud\Bolivar\BolivarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/*==========================================================
============= Rutas cotiza autos con Equidad ===============
===========================================================*/
Route::prefix('autos')->group(function () {
    Route::prefix('equidad')->group(function () {
        Route::get('/getToken', [EquidadController::class, 'getToken']);
        Route::post('/cotizar', [EquidadController::class, 'cotizar']);
        Route::get('/ciudadE/{areaCode}', [EquidadController::class, 'mainCiudadEquidad']);
    });
});

/*==========================================================
============= Rutas cotiza salud con Bolivar ===============
===========================================================*/
Route::prefix('salud')->group(function () {
    Route::prefix('bolivar')->group(function () {
        Route::get('/', [BolivarController::class, 'index']);
        Route::post('/cotizar', [BolivarController::class, 'cotizar']);
    });
});