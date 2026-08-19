@extends('layouts.admin')

@section('title', 'Detail Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('alat.index') }}">Data Alat</a></li>
            <li class="breadcrumb-item active">Detail Alat</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $alat->nama_alat }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kategori:</strong></p>
                            <p><span class="badge bg-info">{{ $alat->kategori?->nama_kategori ?? '-' }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Laboratorium:</strong></p>
                            <p>{{ $alat->laboratorium?->nama_labor ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Merek:</strong></p>
                            <p>{{ $alat->merek ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tipe Pelacakan:</strong></p>
                            <p><span class="badge bg-{{ $alat->tipe_pelacakan == 'unit' ? 'warning' : 'success' }}">{{ ucfirst($alat->tipe_pelacakan) }}</span></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Jumlah Alat:</strong></p>
                            <p>{{ $alat->jumlah_alat }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ketersediaan:</strong></p>
                            <p>{{ $alat->getAvailableQuantity() }} tersedia</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p><strong>Spesifikasi:</strong></p>
                        <p>{{ $alat->spesifikasi ?? '-' }}</p>
                    </div>

                    @if($alat->foto)
                        <div class="mb-3">
                            <p><strong>Foto:</strong></p>
                            <img src="{{ asset('storage/' . $alat->foto) }}" alt="Foto Alat" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        @can('update', $alat)
                        <a href="{{ route('alat.edit', $alat) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        @can('delete', $alat)
                        <form action="{{ route('alat.destroy', $alat) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endcan
                        <a href="{{ route('alat.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($alat->isUnitTracked())
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">Unit Alat ({{ $alat->unitAlat->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @forelse($alat->unitAlat as $unit)
                            <div class="mb-2 pb-2 border-bottom">
                                <p class="mb-1"><strong>{{ $unit->kode_inventaris }}</strong></p>
                                <small class="text-muted">
                                    Status: <span class="badge bg-{{ $unit->status == 'tersedia' ? 'success' : 'warning' }}">{{ ucfirst($unit->status) }}</span>
                                </small>
                            </div>
                        @empty
                            <p class="text-muted text-center">Belum ada unit</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Pengadaan ({{ $alat->pengadaanAlat->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($alat->pengadaanAlat()->latest()->limit(5)->get() as $pengadaan)
                        <div class="mb-2 pb-2 border-bottom">
                            <small class="text-muted">{{ $pengadaan->tanggal_pengadaan->format('d/m/Y') }}</small><br>
                            <small>Jumlah: {{ $pengadaan->jumlah }}</small><br>
                            <small class="text-muted">{{ $pengadaan->supplier }}</small>
                        </div>
                    @empty
                        <p class="text-muted text-center">Belum ada pengadaan</p>
                    @endforelse
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">Peminjaman ({{ $alat->peminjamanAlat->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($alat->peminjamanAlat()->latest()->limit(5)->get() as $peminjaman)
                        <div class="mb-2 pb-2 border-bottom">
                            <small class="text-muted">{{ $peminjaman->waktu_peminjaman->format('d/m/Y') }}</small><br>
                            <small>{{ $peminjaman->userPeminjam?->nama ?? '-' }}</small><br>
                            <small class="badge bg-{{ $peminjaman->status == 'terpinjam' ? 'danger' : 'success' }}">
                                {{ ucfirst(str_replace('_', ' ', $peminjaman->status)) }}
                            </small>
                        </div>
                    @empty
                        <p class="text-muted text-center">Belum ada peminjaman</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
