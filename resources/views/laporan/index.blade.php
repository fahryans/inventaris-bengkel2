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
            <div class="card border-left-primary shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'alat']) }}'">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small font-weight-bold">Total Alat</div>
                    <div class="h3 mb-0">{{ $summary['total_alat'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'bahan']) }}'">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Total Bahan</div>
                    <div class="h3 mb-0">{{ $summary['total_bahan'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'peminjaman']) }}'">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small font-weight-bold">Peminjaman Aktif</div>
                    <div class="h3 mb-0">{{ $summary['peminjaman_aktif'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'peminjaman']) }}'">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Peminjaman Terlambat</div>
                    <div class="h3 mb-0">{{ $summary['peminjaman_terlambat'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>

        @if($user->role !== 'mahasiswa')
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'pemeliharaan']) }}'">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Pemeliharaan Upcoming</div>
                    <div class="h3 mb-0">{{ $summary['pemeliharaan_upcoming'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'pemeliharaan']) }}'">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Pemeliharaan Overdue</div>
                    <div class="h3 mb-0">{{ $summary['pemeliharaan_overdue'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'pengadaan_alat']) }}'">
                <div class="card-body">
                    <div class="text-secondary text-uppercase mb-1 small font-weight-bold">Pengadaan Alat Pending</div>
                    <div class="h3 mb-0">{{ $summary['pengadaan_alat_pending'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'pengadaan_bahan']) }}'">
                <div class="card-body">
                    <div class="text-secondary text-uppercase mb-1 small font-weight-bold">Pengadaan Bahan Pending</div>
                    <div class="h3 mb-0">{{ $summary['pengadaan_bahan_pending'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 cursor-pointer" onclick="location.href='{{ route('laporan.show', ['tipe' => 'bahan']) }}'">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Bahan Low Stock</div>
                    <div class="h3 mb-0">{{ $summary['bahan_low_stock'] }}</div>
                    <small class="text-muted">Klik untuk lihat detail</small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('css')
<style>
    .cursor-pointer {
        cursor: pointer;
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('js')
<script>
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
@endsection
