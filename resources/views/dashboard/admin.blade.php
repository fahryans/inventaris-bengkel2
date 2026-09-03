@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">Dashboard Admin Jurusan</h1>
                    <p class="text-muted">Selamat datang, {{ Auth::user()->nama }}. Kelola sistem inventaris bengkel.</p>
                </div>
                <div>
                    <small class="text-muted">Waktu: <span id="current-time">{{ now()->format('H:i:s') }}</span></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <a href="{{ route('alat.index') }}" class="text-decoration-none">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-primary text-uppercase mb-1 small font-weight-bold">Total Alat</div>
                        <div class="h3 mb-0">{{ $totalAlat }}</div>
                        <small class="text-muted">Klik untuk lihat detail</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a href="{{ route('bahan.index') }}" class="text-decoration-none">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-success text-uppercase mb-1 small font-weight-bold">Total Bahan</div>
                        <div class="h3 mb-0">{{ $totalBahan }}</div>
                        <small class="text-muted">Klik untuk lihat detail</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a href="{{ route('laboratorium.index') }}" class="text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-warning text-uppercase mb-1 small font-weight-bold">Total Lab</div>
                        <div class="h3 mb-0">{{ $totalLaboratorium }}</div>
                        <small class="text-muted">Klik untuk lihat detail</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-info text-uppercase mb-1 small font-weight-bold">Total Pengguna</div>
                        <div class="h3 mb-0">{{ $totalUser }}</div>
                        <small class="text-muted">Klik untuk lihat detail</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Stok Minimum</div>
                    <div class="h3 mb-0">{{ $lowStockBahan }}</div>
                    <a href="{{ route('bahan.index', ['stock_status' => 'low']) }}" class="small text-danger font-weight-bold">Lihat Detail</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Peminjaman Overdue</div>
                    <div class="h3 mb-0">{{ $overduePeminjaman }}</div>
                    <a href="{{ route('peminjaman.index') }}" class="small text-warning font-weight-bold">Lihat Detail</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Maintenance Overdue</div>
                    <div class="h3 mb-0">{{ $overdueMaintenance }}</div>
                    <a href="{{ route('pemeliharaan.index') }}" class="small text-danger font-weight-bold">Lihat Detail</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-info shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('peminjaman.index') }}'">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small font-weight-bold">Total Peminjaman</div>
                    <div class="h3 mb-0">{{ $totalPeminjaman }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Akses Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('alat.create') }}" class="btn btn-primary btn-block btn-sm">
                                <i class="fas fa-plus"></i> Tambah Alat
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('bahan.create') }}" class="btn btn-success btn-block btn-sm">
                                <i class="fas fa-plus"></i> Tambah Bahan
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('users.create') }}" class="btn btn-info btn-block btn-sm">
                                <i class="fas fa-plus"></i> Tambah User
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('pengadaan_alat.create') }}" class="btn btn-secondary btn-block btn-sm">
                                <i class="fas fa-shopping-cart"></i> Pengadaan
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('pemeliharaan.create') }}" class="btn btn-danger btn-block btn-sm">
                                <i class="fas fa-wrench"></i> Maintenance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Peminjaman Terakhir (5)</h6>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @if($recentPeminjaman->count())
                        <div class="table-responsive">
                            <table class="table table-hover table-sm dashboard-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Peminjam</th>
                                        <th>Alat/Bahan</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPeminjaman as $peminjam)
                                        <tr>
                                            <td><strong>{{ $peminjam->userPeminjam->nama }}</strong></td>
                                            <td>{{ $peminjam->equipment_name }}</td>
                                            <td><small>{{ $peminjam->waktu_pengembalian?->format('d-m-Y') }}</small></td>
                                            <td>
                                                <span class="badge badge-{{ $peminjam->status === 'terpinjam' ? 'warning' : 'success' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $peminjam->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Tidak ada data peminjaman</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Distribusi Alat per Lab</h6>
                    <a href="{{ route('alat.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @if($alatPerLab->count())
                        <div class="table-responsive">
                            <table class="table table-hover table-sm dashboard-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Laboratorium</th>
                                        <th>Jumlah Alat</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alatPerLab as $item)
                                        <tr>
                                            <td><strong>{{ $item->laboratorium->nama_labor }}</strong></td>
                                            <td><span class="badge badge-primary">{{ $item->total }}</span></td>
                                            <td>
                                                <a href="{{ route('laboratorium.show', $item->laboratorium) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Tidak ada data laboratorium</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Sistem</h6>
                </div>
                <div class="card-body info-sistem">
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Nama Sistem:</strong> SIMA Bengkel</p>
                            <p><strong>Versi:</strong> 1.0.0</p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>User Role:</strong> <span class="badge badge-info">{{ Auth::user()->role }}</span></p>
                            <p><strong>Status:</strong> <span class="badge badge-success">{{ Auth::user()->status }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Login Sebagai:</strong> {{ Auth::user()->nama }}</p>
                            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                        </div>
                        <div class="col-md-3 text-right">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                            <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-user"></i> Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-1"></i>Distribusi Alat per Laboratorium
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="alatPerLabChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-1"></i>Pengadaan per Bulan ({{ date('Y') }})
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="pengadaanChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('alatPerLabChart'), {
    type: 'bar',
    data: {
        labels: @json($labNames ?? []),
        datasets: [{
            label: 'Jumlah Alat',
            data: @json($alatCounts ?? []),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

new Chart(document.getElementById('pengadaanChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Pengadaan',
            data: @json($pengadaanPerBulan ?? []),
            borderColor: '#4e73df',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

@push('js')
<script>
    setInterval(function() {
        const now = new Date();
        document.getElementById('current-time').textContent = 
            String(now.getHours()).padStart(2, '0') + ':' +
            String(now.getMinutes()).padStart(2, '0') + ':' +
            String(now.getSeconds()).padStart(2, '0');
    }, 1000);

    document.querySelectorAll('.cursor-pointer').forEach(el => {
        el.addEventListener('mouseover', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
        });
        el.addEventListener('mouseout', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
});
</script>
@endpush

@include('dashboard._pinjam_pakai')

@endsection

@push('css')
<style>
    .cursor-pointer {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .dashboard-table .badge {
        color: #1a1a1a !important;
    }
    .dashboard-table td strong,
    .dashboard-table td small {
        color: #1a1a1a !important;
    }
    .info-sistem p {
        color: #1a1a1a !important;
        margin-bottom: 0.5rem;
    }
    .info-sistem .badge {
        color: #1a1a1a !important;
    }
</style>
@endpush
