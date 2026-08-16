@extends('layouts.admin')

@section('title', 'Laporan Saya')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Laporan Saya</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Laporan Saya</h1>
            <p class="text-muted">Riwayat peminjaman dan pemakaian bahan Anda.</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.saya') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="lab" class="form-label">Filter Laboratorium</label>
                    <select name="lab" id="lab" class="form-select">
                        <option value="">Semua Laboratorium</option>
                        @foreach($labs as $lab)
                            <option value="{{ $lab->id }}" {{ request('lab') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->nama_labor }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('laporan.saya') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Peminjaman --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-handshake me-1"></i> Riwayat Peminjaman Alat
            </h6>
            <form method="POST" action="{{ route('laporan.export', 'peminjaman_saya') }}" class="d-inline">
                @csrf
                <input type="hidden" name="lab" value="{{ request('lab') }}">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </form>
        </div>
        <div class="card-body">
            @if($peminjaman->count())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Alat/Unit</th>
                                <th>Laboratorium</th>
                                <th>Keperluan</th>
                                <th>Jumlah</th>
                                <th>Kondisi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peminjaman as $p)
                                <tr>
                                    <td>{{ $p->waktu_peminjaman->format('d-m-Y') }}</td>
                                    <td>{{ $p->equipment_name }}</td>
                                    <td>{{ $p->alat?->laboratorium?->nama_labor ?? $p->unitAlat?->alat?->laboratorium?->nama_labor ?? '-' }}</td>
                                    <td>{{ $p->keperluan }}</td>
                                    <td>{{ $p->jumlah }}</td>
                                    <td>{{ $p->kondisi_saat_peminjaman }} → {{ $p->kondisi_saat_pengembalian ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $p->status === 'terpinjam' ? 'warning' : 'success' }}">
                                            {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $peminjaman->links() }}
            @else
                <div class="alert alert-info mb-0">
                    Tidak ada riwayat peminjaman.
                </div>
            @endif
        </div>
    </div>

    {{-- Tabel Pemakaian Bahan --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-flask me-1"></i> Riwayat Pemakaian Bahan
            </h6>
            <form method="POST" action="{{ route('laporan.export', 'pemakaian_saya') }}" class="d-inline">
                @csrf
                <input type="hidden" name="lab" value="{{ request('lab') }}">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </form>
        </div>
        <div class="card-body">
            @if($pemakaian->count())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Bahan</th>
                                <th>Laboratorium</th>
                                <th>Keperluan</th>
                                <th>Jumlah Diambil</th>
                                <th>Jumlah Terpakai</th>
                                <th>Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pemakaian as $p)
                                <tr>
                                    <td>{{ $p->waktu_pemakaian->format('d-m-Y') }}</td>
                                    <td>{{ $p->bahan->nama_bahan }}</td>
                                    <td>{{ $p->bahan->laboratorium?->nama_labor ?? '-' }}</td>
                                    <td>{{ $p->keperluan }}</td>
                                    <td>{{ $p->jumlah_pengambilan }}</td>
                                    <td>{{ $p->jumlah_terpakai }}</td>
                                    <td>
                                        @if($p->id_user_verifikasi)
                                            <span class="badge bg-success">Terverifikasi</span>
                                        @else
                                            <span class="badge bg-secondary">Menunggu</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $pemakaian->links() }}
            @else
                <div class="alert alert-info mb-0">
                    Tidak ada riwayat pemakaian bahan.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
