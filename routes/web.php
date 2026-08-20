<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AlatController;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LaboratoriumController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PemakaianBahanController;
use App\Http\Controllers\PemeliharaanAlatController;
use App\Http\Controllers\PeminjamanAlatController;
use App\Http\Controllers\PengadaanAlatController;
use App\Http\Controllers\PengadaanBahanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnitAlatController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
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
        Route::resource('laboratorium', LaboratoriumController::class)->except(['create', 'store']);
    });

    Route::middleware(['role:admin_jurusan,kepala_labor,teknisi,kadep'])->group(function () {
        Route::resource('alat', AlatController::class);
        Route::resource('unit-alat', UnitAlatController::class);
        Route::get('unit-alat/{unitAlat}/qr', [\App\Http\Controllers\UnitAlatController::class, 'qr'])
            ->name('unit-alat.qr');
        Route::resource('bahan', BahanController::class);
        Route::resource('kategori', KategoriController::class);
        Route::resource('pemeliharaan', PemeliharaanAlatController::class);
        Route::post('pemeliharaan/{pemeliharaan}/complete', [PemeliharaanAlatController::class, 'complete'])
            ->middleware('throttle:10,1')
            ->name('pemeliharaan.complete');
        Route::resource('pengadaan_alat', PengadaanAlatController::class);
        Route::post('pengadaan_alat/{pengadaan}/mark-received', [PengadaanAlatController::class, 'markReceived'])
            ->middleware('throttle:10,1')
            ->name('pengadaan_alat.mark_received');
        Route::resource('pengadaan_bahan', PengadaanBahanController::class);
        Route::post('pengadaan_bahan/{pengadaan}/mark-received', [PengadaanBahanController::class, 'markReceived'])
            ->middleware('throttle:10,1')
            ->name('pengadaan_bahan.mark_received');
        Route::resource('pemakaian_bahan', PemakaianBahanController::class);
        Route::post('pemakaian_bahan/{pemakaian}/verify', [PemakaianBahanController::class, 'verify'])
            ->middleware('throttle:10,1')
            ->name('pemakaian_bahan.verify');
    });

    Route::middleware(['role:admin_jurusan,kepala_labor,teknisi,dosen,mahasiswa,kadep'])->group(function () {
        Route::get('lab/{lab}', [LabController::class, 'show'])->name('lab.show');
        Route::resource('peminjaman', PeminjamanAlatController::class);
        Route::get('peminjaman/{peminjaman}/return', [PeminjamanAlatController::class, 'returnForm'])
            ->name('peminjaman.return-form');
        Route::post('peminjaman/{peminjaman}/return', [PeminjamanAlatController::class, 'return'])
            ->middleware('throttle:10,1')
            ->name('peminjaman.return');
        Route::get('laporan/saya', [LaporanController::class, 'myReport'])->name('laporan.saya');
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/{tipe}', [LaporanController::class, 'show'])->name('laporan.show');
        Route::post('laporan/{tipe}/export', [LaporanController::class, 'export'])
            ->middleware('throttle:5,1')
            ->name('laporan.export');
    });

    Route::get('/activity-log', [ActivityLogController::class, 'index'])
        ->name('activity-log.index')
        ->middleware('role:admin_jurusan');

});

require __DIR__.'/auth.php';
