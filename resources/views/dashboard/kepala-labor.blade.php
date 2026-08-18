@extends('layouts.admin')

@section('title', 'Dashboard Kepala Lab')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 d-inline-block">Dashboard Kepala Laboratorium</h1>
            <p class="text-muted">{{ $lab->nama_labor }} - {{ $lab->lokasi }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small font-weight-bold">Alat di Lab</div>
                    <div class="h3 mb-0">{{ $totalAlat }}</div>
                    <a href="{{ route('alat.index', ['labor' => $lab->id]) }}" class="small text-muted">Lihat →</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Bahan di Lab</div>
                    <div class="h3 mb-0">{{ $totalBahan }}</div>
                    <a href="{{ route('bahan.index', ['labor' => $lab->id]) }}" class="small text-muted">Lihat →</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Stok Minimum</div>
                    <div class="h3 mb-0">{{ $lowStockBahan }}</div>
                    <a href="{{ route('bahan.index', ['labor' => $lab->id, 'stock_status' => 'low']) }}" class="small text-muted">Lihat →</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Maintenance Segera</div>
                    <div class="h3 mb-0">{{ $upcomingMaintenance }}</div>
                    <a href="{{ route('pemeliharaan.index') }}" class="small text-muted">Lihat →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Peminjaman Aktif di Lab Ini</h6>
                </div>
                <div class="card-body">
                    @if($activePeminjaman->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Peminjam</th>
                                        <th>Alat/Material</th>
                                        <th>Keperluan</th>
                                        <th>Waktu Peminjaman</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activePeminjaman as $peminjam)
                                        <tr>
                                            <td>{{ $peminjam->userPeminjam->nama }}</td>
                                            <td>{{ $peminjam->equipment_name }}</td>
                                            <td>{{ $peminjam->keperluan }}</td>
                                            <td>{{ $peminjam->waktu_peminjaman->format('d-m-Y H:i') }}</td>
                                            <td>{{ $peminjam->waktu_pengembalian?->format('d-m-Y H:i') }}</td>
                                            <td>
                                                @if($peminjam->isOverdue())
                                                    <span class="badge badge-danger">Overdue {{ $peminjam->getDaysOverdue() }} hari</span>
                                                @else
                                                    <span class="badge badge-warning">Terpinjam</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('peminjaman.show', $peminjam) }}" class="btn btn-sm btn-info">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Tidak ada peminjaman aktif</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Akses Cepat</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('alat.create') }}" class="btn btn-primary btn-block mb-2">+ Tambah Alat</a>
                    <a href="{{ route('bahan.create') }}" class="btn btn-success btn-block mb-2">+ Tambah Bahan</a>
                    <a href="{{ route('pengadaan_alat.create') }}" class="btn btn-info btn-block mb-2">+ Pengadaan Alat</a>
                    <a href="{{ route('pemeliharaan.create') }}" class="btn btn-warning btn-block">+ Jadwal Maintenance</a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Info Lab</h6>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong> {{ $lab->nama_labor }}</p>
                    <p><strong>Lokasi:</strong> {{ $lab->lokasi }}</p>
                    <p><strong>Total Alat:</strong> {{ $totalAlat }}</p>
                    <p><strong>Total Bahan:</strong> {{ $totalBahan }}</p>
                    <hr>
                    <p><strong>Login Sebagai:</strong> {{ Auth::user()->nama }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
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
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-1"></i>Peminjaman per Bulan
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="peminjamanLabChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-doughnut me-1"></i>Stok Bahan
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="stokBahanChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('peminjamanLabChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Peminjaman',
            data: @json($peminjamanPerBulan ?? []),
            borderColor: '#1cc88a',
            tension: 0.3
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('stokBahanChart'), {
    type: 'doughnut',
    data: {
        labels: @json($bahanNames ?? []),
        datasets: [{
            data: @json($stokBahan ?? []),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
        }]
    },
    options: { responsive: true }
});
</script>
@endsection

@push('css')
<style>
    .badge {
        color: #1a1a1a !important;
    }
</style>
@endpush
