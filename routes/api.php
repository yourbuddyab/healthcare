<?php

use App\Http\Controllers\Api\v1\AppointmentController;
use App\Http\Controllers\Api\v1\Professional\ProfessionalController;
use App\Http\Controllers\Api\v1\User\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/professional/index', [ProfessionalController::class, 'index'])->name('professional.index');
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/appointment/index', [AppointmentController::class, 'index'])->name('appointment.index');
    });
});
