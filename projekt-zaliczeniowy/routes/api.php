<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IndicatorApiController;
use App\Http\Controllers\Api\NewsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::get('indicators', [IndicatorApiController::class, 'index']);
    Route::get('indicators/export', [IndicatorApiController::class, 'export']);
    Route::get('news', [NewsApiController::class, 'index']);
});
