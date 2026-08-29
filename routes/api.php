<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfileGuruController;
use App\Http\Controllers\ProjekController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'regis']);

Route::get('/guru', [ProfileGuruController::class, 'index']);
Route::get('/portal', [PortalController::class, 'index']);

Route::get('/projek', [ProjekController::class, 'index']);
Route::get('/projek/{projek}', [ProjekController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/guru', [ProfileGuruController::class, 'store']);
    Route::put('/guru/{projek}/update', [ProfileGuruController::class, 'update']);
    Route::delete('/guru/{projek}/delete', [ProfileGuruController::class, 'destroy']);

    Route::post('/portal', [PortalController::class, 'store']);
    Route::put('/portal/{id}/update', [PortalController::class, 'update']);
    Route::delete('/portal/{id}/delete', [PortalController::class, 'destroy']);

    
    Route::post('/projek', [ProjekController::class, 'store']);
    Route::put('/projek/{projek}/update', [ProjekController::class, 'update']);
    Route::delete('/projek/{projek}/delete', [ProjekController::class, 'destroy']);
});