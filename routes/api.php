<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('jwt')->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('reservations', ReservationController::class);

    Route::get('/spaces', [SpaceController::class, 'index']);
    Route::get('/spaces/{id}', [SpaceController::class, 'show']);
    Route::get('/spaces/{spaceId}/available-slots', [AvailabilityController::class, 'getAvailableSlots']);
});

Route::middleware(['jwt', 'role:admin'])->group(function () {
    Route::post('/spaces', [SpaceController::class, 'store']);
    Route::put('/spaces/{id}', [SpaceController::class, 'update']);
    Route::patch('/spaces/{id}', [SpaceController::class, 'update']);
    Route::delete('/spaces/{id}', [SpaceController::class, 'destroy']);
});