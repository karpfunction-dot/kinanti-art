<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =============================================================
// ABSENSI API ROUTES (untuk Scanner Kamera)
// =============================================================
Route::middleware('auth')->prefix('absensi')->group(function () {
    Route::post('/scan', [AbsensiController::class, 'prosesApi'])->name('api.absensi.scan');
});

// Health check endpoint (tanpa auth)
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'environment' => app()->environment()
    ]);
});
