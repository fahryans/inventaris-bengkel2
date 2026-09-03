@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Laporan</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">Dashboard Laporan</h1>
                    <p class="text-muted">Ringkasan data inventaris bengkel.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small font-weight-bold">Total Alat</div>
                    <div class="h3 mb-0">{{ $summary['total_alat'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'alat']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'alat') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'alat') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Total Bahan</div>
                    <div class="h3 mb-0">{{ $summary['total_bahan'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'bahan']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'bahan') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'bahan') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small font-weight-bold">Peminjaman Aktif</div>
                    <div class="h3 mb-0">{{ $summary['peminjaman_aktif'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'peminjaman']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'peminjaman') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'peminjaman') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Peminjaman Terlambat</div>
                    <div class="h3 mb-0">{{ $summary['peminjaman_terlambat'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'peminjaman']) }}?filter=terlambat" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'peminjaman') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'peminjaman') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

@if($user->role === 'mahasiswa' || $user->role === 'dosen')
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Peminjaman Dikembalikan</div>
                    <div class="h3 mb-0">{{ $summary['peminjaman_dikembalikan'] ?? 0 }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'peminjaman_dikembalikan']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <form method="POST" action="{{ route('laporan.export', 'peminjaman_dikembalikan') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf"></i> PDF</button>
                        </form>
                        <a href="{{ route('export', 'peminjaman_dikembalikan') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small font-weight-bold">Catatan Pemakaian Bahan</div>
                    <div class="h3 mb-0">{{ $summary['pemakaian_saya'] ?? 0 }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'pemakaian_saya']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <form method="POST" action="{{ route('laporan.export', 'pemakaian_saya') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf"></i> PDF</button>
                        </form>
                        <a href="{{ route('export', 'pemakaian_saya') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user->role !== 'mahasiswa')
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Pemeliharaan Upcoming</div>
                    <div class="h3 mb-0">{{ $summary['pemeliharaan_upcoming'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'pemeliharaan']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'pemeliharaan') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'pemeliharaan') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Pemeliharaan Overdue</div>
                    <div class="h3 mb-0">{{ $summary['pemeliharaan_overdue'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'pemeliharaan']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'pemeliharaan') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'pemeliharaan') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-secondary text-uppercase mb-1 small font-weight-bold">Pengadaan Alat Pending</div>
                    <div class="h3 mb-0">{{ $summary['pengadaan_alat_pending'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'pengadaan_alat']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'pengadaan_alat') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'pengadaan_alat') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-secondary text-uppercase mb-1 small font-weight-bold">Pengadaan Bahan Pending</div>
                    <div class="h3 mb-0">{{ $summary['pengadaan_bahan_pending'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'pengadaan_bahan']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'pengadaan_bahan') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'pengadaan_bahan') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Bahan Low Stock</div>
                    <div class="h3 mb-0">{{ $summary['bahan_low_stock'] }}</div>
                    <a href="{{ route('laporan.show', ['tipe' => 'bahan']) }}" class="small text-muted d-block mb-2">Klik untuk lihat detail →</a>
                    <div class="mt-2">
                        <a href="{{ route('export', 'bahan') }}?format=pdf" class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ route('export', 'bahan') }}?format=excel" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($user->role !== 'mahasiswa')
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-4">
                <i class="fas fa-chart-bar"></i> Laporan Breakdown per Merek & Supplier
            </h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <a href="{{ route('laporan.breakdown_alat') }}" class="text-decoration-none">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-info text-uppercase mb-1 small font-weight-bold">
                            <i class="fas fa-tools"></i> Breakdown Alat per Merek
                        </div>
                        <p class="mb-0">Lihat detail pengadaan alat berdasarkan merek & supplier</p>
                        <small class="text-muted">Klik untuk lihat laporan</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="{{ route('laporan.breakdown_bahan') }}" class="text-decoration-none">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-success text-uppercase mb-1 small font-weight-bold">
                            <i class="fas fa-flask"></i> Breakdown Bahan per Merek
                        </div>
                        <p class="mb-0">Lihat detail pengadaan bahan berdasarkan merek & supplier</p>
                        <small class="text-muted">Klik untuk lihat laporan</small>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endif

@push('css')
<style>
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
</style>
@endpush
@endsection
