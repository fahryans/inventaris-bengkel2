@extends('layouts.admin')

@section('title', 'Dashboard Kadep')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 d-inline-block">Dashboard Kepala Departemen</h1>
            <p class="text-muted">Laporan Sistem Inventaris</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small font-weight-bold">Total Alat</div>
                    <div class="h3 mb-0">{{ $totalAlat }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Total Bahan</div>
                    <div class="h3 mb-0">{{ $totalBahan }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Total Lab</div>
                    <div class="h3 mb-0">{{ $totalLaboratorium }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small font-weight-bold">Total Peminjaman</div>
                    <div class="h3 mb-0">{{ $totalPeminjaman }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Bahan dengan Stok Minimum</div>
                    <div class="h3 mb-0">{{ $lowStockBahan }}</div>
                    <a href="{{ route('bahan.index', ['stock_status' => 'low']) }}" class="small text-muted">Lihat Detail →</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-secondary text-uppercase mb-1 small font-weight-bold">Akses Laporan</div>
                    <div class="mt-3">
                        <a href="{{ route('alat.index') }}" class="btn btn-sm btn-info">Lihat Alat</a>
                        <a href="{{ route('bahan.index') }}" class="btn btn-sm btn-warning">Lihat Bahan</a>
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-primary">Lihat Peminjaman</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Peminjaman Tahun {{ now()->year }}</h6>
                </div>
                <div class="card-body">
                    @if($peminjamPerBulan->count())
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="peminjamChart"></canvas>
                        </div>
                    @else
                        <p class="text-muted">Data peminjaman belum tersedia</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Akses Laporan</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="{{ route('alat.index') }}">Daftar Alat Lengkap</a></li>
                        <li class="list-group-item"><a href="{{ route('bahan.index') }}">Daftar Bahan Lengkap</a></li>
                        <li class="list-group-item"><a href="{{ route('laboratorium.index') }}">Daftar Laboratorium</a></li>
                        <li class="list-group-item"><a href="{{ route('users.index') }}">Daftar Pengguna</a></li>
                        <li class="list-group-item"><a href="{{ route('peminjaman.index') }}">Riwayat Peminjaman</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ringkasan Sistem</h6>
                </div>
                <div class="card-body">
                    <p><strong>Total Alat Terdaftar:</strong> {{ $totalAlat }} unit</p>
                    <p><strong>Total Bahan Terdaftar:</strong> {{ $totalBahan }} item</p>
                    <p><strong>Total Laboratorium:</strong> {{ $totalLaboratorium }} lab</p>
                    <p><strong>Total Transaksi Peminjaman:</strong> {{ $totalPeminjaman }} transaksi</p>
                    <p><strong>Bahan dengan Stok Kritis:</strong> {{ $lowStockBahan }} item</p>
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

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($peminjamPerBulan->count())
            const ctx = document.getElementById('peminjamChart').getContext('2d');
            const data = {
                labels: [
                    @foreach($peminjamPerBulan as $item)
                        '{{ \Carbon\Carbon::createFromDate(now()->year, $item->bulan, 1)->format('F') }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: [
                        @foreach($peminjamPerBulan as $item)
                            {{ $item->total }},
                        @endforeach
                    ],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.1
                }]
            };

            new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif
    });
</script>
@endpush

@endsection
