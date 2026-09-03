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
use App\Http\Controllers\ExportController;
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
        // Bulk User Creation (HARUS sebelum resource)
        Route::get('users/bulk-create', [UserController::class, 'bulkCreate'])
            ->name('users.bulk-create');
        Route::post('users/bulk-store', [UserController::class, 'bulkStore'])
            ->name('users.bulk-store');
        Route::post('users/import-preview', [UserController::class, 'importPreview'])
            ->name('users.import-preview');
        Route::post('users/import-store', [UserController::class, 'importStore'])
            ->name('users.import-store');
        Route::get('users/template-excel', [UserController::class, 'downloadTemplate'])
            ->name('users.template-excel');

        Route::resource('users', UserController::class);
        Route::resource('laboratorium', LaboratoriumController::class)->except(['create', 'store']);

        // Update SOP laboratory (rich text HTML)
        Route::put('lab/{lab}/sop', [LabController::class, 'updateSop'])
            ->name('lab.sop.update');
    });

    Route::middleware(['role:admin_jurusan,kepala_labor,teknisi,kadep,mahasiswa,dosen'])->group(function () {
        Route::resource('alat', AlatController::class);

        // Spesifikasi Alat Management
        Route::post('alat/{alat}/spesifikasi', [AlatController::class, 'storeSpesifikasi'])
            ->name('alat.spesifikasi.store');
        Route::put('alat/{alat}/spesifikasi/{spesifikasi}', [AlatController::class, 'updateSpesifikasi'])
            ->name('alat.spesifikasi.update');
        Route::delete('alat/{alat}/spesifikasi/{spesifikasi}', [AlatController::class, 'destroySpesifikasi'])
            ->name('alat.spesifikasi.destroy');

        Route::resource('unit-alat', UnitAlatController::class);
        Route::get('unit-alat/{unitAlat}/qr', [\App\Http\Controllers\UnitAlatController::class, 'qr'])
            ->name('unit-alat.qr');
        Route::resource('bahan', BahanController::class);

        // Spesifikasi Bahan Management
        Route::post('bahan/{bahan}/spesifikasi', [BahanController::class, 'storeSpesifikasi'])
            ->name('bahan.spesifikasi.store');
        Route::put('bahan/{bahan}/spesifikasi/{spesifikasi}', [BahanController::class, 'updateSpesifikasi'])
            ->name('bahan.spesifikasi.update');
        Route::delete('bahan/{bahan}/spesifikasi/{spesifikasi}', [BahanController::class, 'destroySpesifikasi'])
            ->name('bahan.spesifikasi.destroy');

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
        Route::post('pemakaian_bahan/{pemakaian}/return', [PemakaianBahanController::class, 'returnBahan'])
            ->middleware('throttle:10,1')
            ->name('pemakaian_bahan.return');
        Route::post('pemakaian_bahan/{pemakaian}/verify-return', [PemakaianBahanController::class, 'verifyReturn'])
            ->middleware('throttle:10,1')
            ->name('pemakaian_bahan.verify_return');
        Route::post('pemakaian_bahan/{pemakaian}/reject-return', [PemakaianBahanController::class, 'rejectReturn'])
            ->middleware('throttle:10,1')
            ->name('pemakaian_bahan.reject_return');
    });

    Route::middleware(['role:admin_jurusan,kepala_labor,teknisi,dosen,mahasiswa,kadep'])->group(function () {
        Route::get('lab/{lab}', [LabController::class, 'show'])->name('lab.show');
        // Peminjaman / pemakaian massal di halaman lab
        Route::post('lab/{lab}/borrow-mass', [LabController::class, 'borrowMass'])
            ->middleware('throttle:60,1')
            ->name('lab.borrow_mass');
        Route::resource('peminjaman', PeminjamanAlatController::class);
        Route::post('peminjaman/quick', [PeminjamanAlatController::class, 'quickStore'])
            ->middleware('throttle:10,1')
            ->name('peminjaman.quick');
        Route::get('peminjaman/{peminjaman}/return', [PeminjamanAlatController::class, 'returnForm'])
            ->name('peminjaman.return-form');
        Route::post('peminjaman/{peminjaman}/return', [PeminjamanAlatController::class, 'return'])
             ->middleware('throttle:10,1')
             ->name('peminjaman.return');
         Route::get('laporan/saya', [LaporanController::class, 'myReport'])->name('laporan.saya');
         Route::get('laporan/breakdown/alat', [LaporanController::class, 'breakdownMerekAlat'])->name('laporan.breakdown_alat');
         Route::get('laporan/breakdown/bahan', [LaporanController::class, 'breakdownMerekBahan'])->name('laporan.breakdown_bahan');
         Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
         Route::get('laporan/{tipe}', [LaporanController::class, 'show'])->name('laporan.show');
         Route::post('laporan/{tipe}/export', [LaporanController::class, 'export'])
             ->middleware('throttle:5,1')
             ->name('laporan.export');
    });

    // Export routes
    Route::middleware(['role:admin_jurusan,kepala_labor,teknisi,kadep,dosen,mahasiswa'])->group(function () {
        Route::get('export/{tipe}', [ExportController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('export');
    });

});

require __DIR__.'/auth.php';
