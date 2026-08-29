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

Route::get('/user', [UserController::class, 'index']);
Route::get('/user/{user}', [UserController::class, 'show']);

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'regis']);

Route::get('/guru', [ProfileGuruController::class, 'index']);

Route::get('/portal', [PortalController::class, 'index']);
Route::get('/portal/{portal}', [PortalController::class, 'index']);

Route::get('/projek', [ProjekController::class, 'index']);
Route::get('/projek/{projek}', [ProjekController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{user}/update', [UserController::class,'update']);
    Route::delete('/user/{user}/delete', [UserController::class,'destroy']);

    Route::post('/guru', [ProfileGuruController::class, 'store']);
    Route::put('/guru/{guru}/update', [ProfileGuruController::class, 'update']);
    Route::delete('/guru/{guru}/delete', [ProfileGuruController::class, 'destroy']);

    Route::post('/portal', [PortalController::class, 'store']);
    Route::put('/portal/{portal}/update', [PortalController::class, 'update']);
    Route::delete('/portal/{portal}/delete', [PortalController::class, 'destroy']);

    
    Route::post('/projek', [ProjekController::class, 'store']);
    Route::put('/projek/{projek}/update', [ProjekController::class, 'update']);
    Route::delete('/projek/{projek}/delete', [ProjekController::class, 'destroy']);
});