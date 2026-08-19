<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AlatController;
use App\Http\Controllers\Api\BahanController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\LaboratoriumController;
use App\Http\Controllers\Api\UnitAlatController;
use App\Http\Controllers\Api\PeminjamanAlatController;
use App\Http\Controllers\Api\PengadaanAlatController;
use App\Http\Controllers\Api\PengadaanBahanController;
use App\Http\Controllers\Api\PemakaianBahanController;
use App\Http\Controllers\Api\PemeliharaanAlatController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('api.role:admin_jurusan');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Admin only
    Route::middleware('api.role:admin_jurusan,kadep,kepala_labor')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Lab management
    Route::apiResource('laboratorium', LaboratoriumController::class)
        ->only(['index', 'show', 'update']);

    // Equipment management
    Route::middleware('api.role:admin_jurusan,kepala_labor,teknisi,kadep')->group(function () {
        Route::apiResource('alat', AlatController::class);
        Route::apiResource('unit-alat', UnitAlatController::class);
        Route::apiResource('bahan', BahanController::class);
        Route::apiResource('kategori', KategoriController::class);
        Route::apiResource('pengadaan-alat', PengadaanAlatController::class);
        Route::apiResource('pengadaan-bahan', PengadaanBahanController::class);
        Route::apiResource('pemakaian-bahan', PemakaianBahanController::class);
        Route::apiResource('pemeliharaan', PemeliharaanAlatController::class);
    });

    // Borrowing (all roles)
    Route::apiResource('peminjaman', PeminjamanAlatController::class);

    // Reports
    Route::get('/laporan/{tipe}', [LaporanController::class, 'show']);
    Route::get('/dashboard', [LaporanController::class, 'dashboard']);
});