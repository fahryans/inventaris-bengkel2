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
Route::name('api.')->middleware('auth:sanctum')->group(function () {
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
        Route::post('pengadaan-alat/{pengadaan_alat}/mark-received', [PengadaanAlatController::class, 'markReceived']);
        Route::apiResource('pengadaan-bahan', PengadaanBahanController::class);
        Route::post('pengadaan-bahan/{pengadaan_bahan}/mark-received', [PengadaanBahanController::class, 'markReceived']);
        Route::apiResource('pemakaian-bahan', PemakaianBahanController::class);
        Route::post('pemakaian-bahan/{pemakaian_bahan}/verify', [PemakaianBahanController::class, 'verify']);
        Route::apiResource('pemeliharaan', PemeliharaanAlatController::class);
        Route::post('pemeliharaan/{pemeliharaan}/complete', [PemeliharaanAlatController::class, 'complete']);
    });

    // Borrowing (all roles)
    Route::apiResource('peminjaman', PeminjamanAlatController::class);
    Route::post('peminjaman/{peminjaman}/return', [PeminjamanAlatController::class, 'return']);

    // Reports
    Route::get('/laporan/{tipe}', [LaporanController::class, 'show']);
    Route::get('/dashboard', [LaporanController::class, 'dashboard']);
});