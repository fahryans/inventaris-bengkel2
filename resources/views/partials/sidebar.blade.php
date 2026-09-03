<aside id="sidebar" class="sidebar">

    {{-- Logo --}}
    <div class="sidebar-header">

        <h3>

            <img src="{{ asset('images/unplogo.png') }}" alt="Logo UNP" style="height: 48px; width: auto;" class="me-0.2">

              SIMA Bengkel

        </h3>

        <button class="sidebar-close d-lg-none" onclick="toggleSidebar()" aria-label="Tutup sidebar">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>

    </div>

    {{-- Menu --}}
    <nav aria-label="Sidebar navigation">
    <ul class="sidebar-menu">

        {{-- Dashboard --}}
        <li>

            <a href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">

                <i class="fas fa-house"></i>

                Dashboard

            </a>

        </li>

        @can('viewAny', App\Models\User::class)
        <li>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                User
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\Laboratorium::class)
        <li>
            <a href="{{ route('laboratorium.index') }}" class="{{ request()->routeIs('laboratorium.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                Laboratorium
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\Kategori::class)
        <li>
            <a href="{{ route('kategori.index') }}" class="{{ request()->routeIs('kategori.*') ? 'active' : '' }}">
                <i class="fas fa-folder"></i>
                Kategori
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\Alat::class)
        <li>
            <a href="{{ route('alat.index') }}" class="{{ request()->routeIs('alat.*') ? 'active' : '' }}">
                <i class="fas fa-screwdriver-wrench"></i>
                Alat
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\UnitAlat::class)
        <li>
            <a href="{{ route('unit-alat.index') }}" class="{{ request()->routeIs('unit-alat.*') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked"></i>
                Unit Alat
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\Bahan::class)
        <li>
            <a href="{{ route('bahan.index') }}" class="{{ request()->routeIs('bahan.*') ? 'active' : '' }}">
                <i class="fas fa-flask"></i>
                Bahan
            </a>
        </li>
        @endcan

        {{-- TRANSAKSI --}}
        <li class="menu-title">

            TRANSAKSI

        </li>

        @can('viewAny', App\Models\PengadaanAlat::class)
        <li>
            <a href="{{ route('pengadaan_alat.index') }}" class="{{ request()->routeIs('pengadaan_alat.*') ? 'active' : '' }}">
                <i class="fas fa-cart-plus"></i>
                Pengadaan Alat
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\PengadaanBahan::class)
        <li>
            <a href="{{ route('pengadaan_bahan.index') }}" class="{{ request()->routeIs('pengadaan_bahan.*') ? 'active' : '' }}">
                <i class="fas fa-cart-arrow-down"></i>
                Pengadaan Bahan
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\PeminjamanAlat::class)
        <li>
            <a href="{{ route('peminjaman.index') }}" class="{{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                <i class="fas fa-handshake"></i>
                Peminjaman
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\PemakaianBahan::class)
        <li>
            <a href="{{ route('pemakaian_bahan.index') }}" class="{{ request()->routeIs('pemakaian_bahan.*') ? 'active' : '' }}">
                <i class="fas fa-vial"></i>
                Pemakaian
            </a>
        </li>
        @endcan

        @can('viewAny', App\Models\PemeliharaanAlat::class)
        <li>
            <a href="{{ route('pemeliharaan.index') }}" class="{{ request()->routeIs('pemeliharaan.*') ? 'active' : '' }}">
                <i class="fas fa-screwdriver"></i>
                Pemeliharaan
            </a>
        </li>
        @endcan

        {{-- LAPORAN --}}
        <li class="menu-title">

            LAPORAN

        </li>

        <li>
            <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                <i class="fas fa-chart-column"></i>
                Laporan
            </a>
        </li>

        {{-- PENGATURAN --}}
        <li class="menu-title">

            PENGATURAN

        </li>

        <li>

            <a href="{{ route('profile.edit') }}">

                <i class="fas fa-user-gear"></i>

                Profil

            </a>

        </li>

    </ul>
    </nav>

</aside>

<style>

.sidebar{

    width:260px;

    background:#B45F06;

    position:fixed;

    left:0;

    top:0;

    bottom:0;

    overflow-y:auto;

    color:white;

    z-index:1000;

}

.sidebar-header{

    padding:25px;

    font-size:22px;

    font-weight:600;

    border-bottom:1px solid rgba(255,255,255,.2);

    background:#78350F;

    color:#f5f0e9;

}

.sidebar-menu{

    list-style:none;

    margin:0;

    padding:15px 0;

}

.sidebar-menu li{

    margin:4px 0;

}

.sidebar-menu li a{

    display:flex;

    align-items:center;

    gap:15px;

    color:#f5f0e9;

    text-decoration:none;

    padding:12px 25px;

    transition:.3s;

}

.sidebar-menu li a:hover{

    background:#78350F;

    color:#f5f0e9;

}

.sidebar-menu li a.active{

    background:#78350F;

    color:#f5f0e9;

    border-left:4px solid #B45F06;

}

.menu-title{

    padding:18px 25px 8px;

    font-size:12px;

    color:#f5f0e9;

    text-transform:uppercase;

    font-weight:600;

    letter-spacing:1px;

}

@media(max-width:991px){

.sidebar{

transform:translateX(-100%);

transition:.3s;

}

.sidebar.show{

transform:translateX(0);

}

.sidebar-close{

position:absolute;

right:15px;

top:25px;

background:none;

border:none;

color:white;

font-size:18px;

cursor:pointer;

display:none;

}

.sidebar-close{

display:block;

}

}

@media(min-width:992px){

.sidebar-close{

display:none !important;

}

}

</style>