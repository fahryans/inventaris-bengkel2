<?php

use App\Http\Controllers\AlatController;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaboratoriumController;
use App\Http\Controllers\PemakaianBahanController;
use App\Http\Controllers\PemeliharaanAlatController;
use App\Http\Controllers\Pinjam_alat;
use App\Http\Controllers\PengadaanAlatController;
use App\Http\Controllers\PengadaanBahanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnitAlatController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::middleware(['role:admin_jurusan,kepala_labor,kadep'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('laboratorium', LaboratoriumController::class);
    });

    Route::middleware(['role:admin_jurusan,kepala_labor,teknisi,kadep'])->group(function () {
        Route::resource('alat', AlatController::class);
        Route::resource('unit-alat', UnitAlatController::class);
        Route::resource('bahan', BahanController::class);
        Route::resource('kategori', KategoriController::class);
        Route::resource('pemeliharaan', PemeliharaanAlatController::class);
        Route::post('pemeliharaan/{pemeliharaan}/complete', [PemeliharaanAlatController::class, 'complete'])
            ->name('pemeliharaan.complete');
        Route::resource('pengadaan_alat', PengadaanAlatController::class);
        Route::post('pengadaan_alat/{pengadaan}/mark-received', [PengadaanAlatController::class, 'markReceived'])
            ->name('pengadaan_alat.mark_received');
        Route::resource('pengadaan_bahan', PengadaanBahanController::class);
        Route::post('pengadaan_bahan/{pengadaan}/mark-received', [PengadaanBahanController::class, 'markReceived'])
            ->name('pengadaan_bahan.mark_received');
        Route::resource('pemakaian_bahan', PemakaianBahanController::class);
        Route::post('pemakaian_bahan/{pemakaian}/verify', [PemakaianBahanController::class, 'verify'])
            ->name('pemakaian_bahan.verify');
    });

    Route::middleware(['role:admin_jurusan,kepala_labor,teknisi,dosen,mahasiswa,kadep'])->group(function () {
        Route::resource('peminjaman', Pinjam_alat::class);
        Route::post('peminjaman/{peminjaman}/return', [Pinjam_alat::class, 'return'])
            ->name('peminjaman.return');
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/{tipe}', [LaporanController::class, 'show'])->name('laporan.show');
        Route::post('laporan/{tipe}/export', [LaporanController::class, 'export'])->name('laporan.export');
    });

});

require __DIR__.'/auth.php';