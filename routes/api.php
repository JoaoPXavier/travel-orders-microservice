<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    // Travel Orders routes
    Route::apiResource('travel-orders', TravelOrderController::class);
    
    // Update travel order status (special endpoint for approvers)
    Route::patch('/travel-orders/{travel_order}/status', [TravelOrderController::class, 'updateStatus']);
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'service' => 'Travel Orders Microservice'
    ]);
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint não encontrado.'
    ], 404);
});