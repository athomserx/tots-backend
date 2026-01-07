<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('jwt')->group(function () {
  Route::get('/user', [AuthController::class, 'getUser']);
  Route::post('/logout', [AuthController::class, 'logout']);

  Route::apiResource('spaces', SpaceController::class);
  Route::apiResource('reservations', ReservationController::class);
});