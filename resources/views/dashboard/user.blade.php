@extends('layouts.admin')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3">Dashboard {{ ucfirst(Auth::user()->role) }}</h1>
            <p class="text-muted">Selamat datang, {{ Auth::user()->nama }}. Pilih laboratorium untuk meminjam alat atau bahan.</p>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small font-weight-bold">Sedang Dipinjam</div>
                    <div class="h3 mb-0">{{ $activePeminjaman->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Total Laboratorium</div>
                    <div class="h3 mb-0">{{ $labs->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small font-weight-bold">Riwayat Peminjaman</div>
                    <div class="h3 mb-0">{{ $myPeminjaman->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Total Alat Tersedia</div>
                    <div class="h3 mb-0">{{ $labs->sum('alat_count') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Laboratorium --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Daftar Laboratorium</h5>
            </div>
        </div>

        @forelse($labs as $lab)
            <div class="col-md-4 mb-4">
                <a href="{{ route('lab.show', $lab) }}" class="text-decoration-none">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-building fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">{{ $lab->nama_labor }}</h5>
                                    <small class="text-muted">{{ $lab->lokasi }}</small>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border rounded py-2">
                                        <div class="h5 mb-0 text-primary">{{ $lab->alat_count }}</div>
                                        <small class="text-muted">Alat</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded py-2">
                                        <div class="h5 mb-0 text-success">{{ $lab->bahan_count }}</div>
                                        <small class="text-muted">Bahan</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <small class="text-muted">Klik untuk melihat detail →</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    Belum ada laboratorium yang tersedia.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Peminjaman Aktif --}}
    @if($activePeminjaman->count())
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Peminjaman Aktif</h6>
                    <a href="{{ route('laporan.saya') }}" class="btn btn-sm btn-outline-primary">Lihat Semua →</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Alat/Unit</th>
                                    <th>Keperluan</th>
                                    <th>Dipinjam Sejak</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activePeminjaman as $peminjaman)
                                    <tr>
                                        <td>{{ $peminjaman->equipment_name }}</td>
                                        <td>{{ $peminjaman->keperluan }}</td>
                                        <td>{{ $peminjaman->waktu_peminjaman->format('d-m-Y H:i') }}</td>
                                        <td>
                                            {{ $peminjaman->waktu_pengembalian?->format('d-m-Y H:i') }}
                                            @if($peminjaman->isOverdue())
                                                <span class="badge bg-danger">{{ $peminjaman->getDaysOverdue() }} hari overdue</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-warning">Terpinjam</span></td>
                                        <td>
                                            <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-sm btn-info">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Riwayat Peminjaman Chart --}}
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-1"></i>Riwayat Peminjaman Saya ({{ date('Y') }})
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="riwayatPeminjamanChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    new Chart(document.getElementById('riwayatPeminjamanChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Peminjaman',
                data: @json($riwayatPeminjaman ?? []),
                borderColor: '#4e73df',
                tension: 0.3,
                fill: true,
                backgroundColor: 'rgba(78, 115, 223, 0.1)'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
    </script>
</div>
@endsection
