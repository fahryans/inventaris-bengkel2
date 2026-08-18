<?php

namespace App\Providers;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\PemakaianBahan;
use App\Models\PemeliharaanAlat;
use App\Models\PeminjamanAlat;
use App\Models\PengadaanAlat;
use App\Models\PengadaanBahan;
use App\Models\UnitAlat;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\AlatPolicy;
use App\Policies\BahanPolicy;
use App\Policies\KategoriPolicy;
use App\Policies\LaboratoriumPolicy;
use App\Policies\PemakaianBahanPolicy;
use App\Policies\PemeliharaanAlatPolicy;
use App\Policies\PeminjamanAlatPolicy;
use App\Policies\PengadaanAlatPolicy;
use App\Policies\PengadaanBahanPolicy;
use App\Policies\UnitAlatPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Alat::class => AlatPolicy::class,
        Bahan::class => BahanPolicy::class,
        Kategori::class => KategoriPolicy::class,
        Laboratorium::class => LaboratoriumPolicy::class,
        UnitAlat::class => UnitAlatPolicy::class,
        User::class => UserPolicy::class,
        PeminjamanAlat::class => PeminjamanAlatPolicy::class,
        PemeliharaanAlat::class => PemeliharaanAlatPolicy::class,
        PengadaanAlat::class => PengadaanAlatPolicy::class,
        PengadaanBahan::class => PengadaanBahanPolicy::class,
        PemakaianBahan::class => PemakaianBahanPolicy::class,
        Activity::class => ActivityPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin_jurusan';
        });

        Gate::define('is-kepala-labor', function (User $user) {
            return $user->role === 'kepala_labor';
        });

        Gate::define('is-teknisi', function (User $user) {
            return $user->role === 'teknisi';
        });

        Gate::define('can-manage-inventory', function (User $user) {
            return in_array($user->role, ['admin_jurusan', 'kepala_labor', 'teknisi']);
        });

        Gate::define('can-manage-users', function (User $user) {
            return in_array($user->role, ['admin_jurusan', 'kadep']);
        });

        Gate::define('can-borrow', function (User $user) {
            return in_array($user->role, ['admin_jurusan', 'kepala_labor', 'teknisi', 'dosen', 'mahasiswa']);
        });
    }

    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
