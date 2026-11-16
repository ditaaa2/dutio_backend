
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\CrewPointController;

// Route untuk mengecek koneksi API DUTIO
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API DUTIO berhasil terhubung!',
    ]);
});

// Register dan Login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route yang harus menggunakan token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Update Password
    Route::post('/change-password', [AuthController::class, 'updatePassword']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Jadwal
    Route::get('/jadwal', [JadwalController::class, 'index']);

    // Checklist
    Route::get('/checklist', [ChecklistController::class, 'index']);
    Route::post('/checklist/{id}/toggle', [ChecklistController::class, 'toggle']);

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::post('/laporan', [LaporanController::class, 'store']);

    // Crew Points
    Route::get('/crew-points', [CrewPointController::class, 'index']);
});
