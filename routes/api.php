<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfileGuruController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/guru', [ProfileGuruController::class, 'store']);
    Route::put('/guru/edit/{id}', [ProfileGuruController::class, 'update']);
    Route::delete('/guru/delete/{id}', [ProfileGuruController::class, 'destroy']);

    Route::post('/portal', [PortalController::class, 'store']);
    Route::put('/portal/edit/{id}', [PortalController::class, 'update']);
    Route::delete('/portal/delete/{id}', [PortalController::class, 'destroy']);
});