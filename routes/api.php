<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('jwt')->group(function () {
  Route::get('/user', [AuthController::class, 'getUser']);
  Route::post('/logout', [AuthController::class, 'logout']);
});