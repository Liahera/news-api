<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\PublicNewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/', static function () {
        return response()->json(['message' => 'API v1 is working']);
    });

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('profile', [AuthController::class, 'me']);
            Route::put('profile', [AuthController::class, 'update']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::prefix('public')->group(function () {
        Route::get('news', [NewsController::class, 'publicIndex']);
        Route::get('news/{id}', [NewsController::class, 'publicShow']);
    });

    Route::middleware('auth:sanctum')->prefix('news')->group(function () {
        Route::get('my', [NewsController::class, 'index']);
        Route::post('store', [NewsController::class, 'store']);
        Route::get('{news}', [NewsController::class, 'show']);
        Route::put('{news}', [NewsController::class, 'update']);
        Route::patch('{news}/status', [NewsController::class, 'changeStatus']);
    });
});
