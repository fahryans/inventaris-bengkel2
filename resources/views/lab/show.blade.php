@extends('layouts.admin')

@section('title', $lab->nama_labor)

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">{{ $lab->nama_labor }}</li>
        </ol>
    </nav>

    {{-- Info Lab --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-4" style="width: 70px; height: 70px;">
                    <i class="fas fa-building fa-2x"></i>
                </div>
                <div>
                    <h3 class="mb-1">{{ $lab->nama_labor }}</h3>
                    <p class="text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i> {{ $lab->lokasi }}</p>
                    @if($lab->sop)
                        <small class="text-muted">SOP: {{ $lab->sop }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Alat --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-screwdriver-wrench me-1"></i> Daftar Alat ({{ $lab->alat->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($lab->alat->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Alat</th>
                                        <th>Merek</th>
                                        <th>Tipe</th>
                                        <th>Stok/Unit</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lab->alat as $alat)
                                        <tr>
                                            <td>{{ $alat->nama_alat }}</td>
                                            <td>{{ $alat->merek ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $alat->tipe_pelacakan === 'agregat' ? 'primary' : 'info' }}">
                                                    {{ ucfirst($alat->tipe_pelacakan) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($alat->tipe_pelacakan === 'agregat')
                                                    {{ $alat->jumlah_alat }} unit
                                                @else
                                                    {{ $alat->unitAlat->where('status', 'tersedia')->count() }} / {{ $alat->unitAlat->count() }} tersedia
                                                @endif
                                            </td>
                                            <td>
                                                @can('create', \App\Models\PeminjamanAlat::class)
                                                    @if($alat->tipe_pelacakan === 'agregat' && $alat->jumlah_alat > 0)
                                                        <a href="{{ route('peminjaman.create', ['lab_id' => $lab->id, 'alat_id' => $alat->id]) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-handshake"></i> Pinjam
                                                        </a>
                                                    @elseif($alat->tipe_pelacakan === 'unit' && $alat->unitAlat->where('status', 'tersedia')->count() > 0)
                                                        <a href="{{ route('peminjaman.create', ['lab_id' => $lab->id, 'alat_id' => $alat->id]) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-handshake"></i> Pinjam
                                                        </a>
                                                    @else
                                                        <span class="badge bg-secondary">Stok Habis</span>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            Belum ada alat di laboratorium ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Bahan --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-flask me-1"></i> Daftar Bahan ({{ $lab->bahan->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($lab->bahan->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Bahan</th>
                                        <th>Merek</th>
                                        <th>Stok</th>
                                        <th>Satuan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lab->bahan as $bahan)
                                        <tr>
                                            <td>{{ $bahan->nama_bahan }}</td>
                                            <td>{{ $bahan->merek ?? '-' }}</td>
                                            <td>{{ $bahan->stok_saat_ini }}</td>
                                            <td>{{ $bahan->satuan }}</td>
                                            <td>
                                                @if($bahan->isStokMenipis())
                                                    <span class="badge bg-danger">Stok Menipis</span>
                                                @else
                                                    <span class="badge bg-success">Aman</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            Belum ada bahan di laboratorium ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
