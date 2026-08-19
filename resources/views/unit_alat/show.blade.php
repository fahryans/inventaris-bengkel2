@extends('layouts.admin')

@section('title', 'Detail Unit Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('unit-alat.index') }}">Data Unit Alat</a></li>
            <li class="breadcrumb-item active">Detail Unit Alat</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $unitAlat->kode_inventaris }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kode Inventaris:</strong></p>
                            <p>{{ $unitAlat->kode_inventaris }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Nama Alat:</strong></p>
                            <p>{{ $unitAlat->alat->nama_alat }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kondisi Saat Ini:</strong></p>
                            <p>
                                @if($unitAlat->kondisi_saat_ini == 'baik')
                                    <span class="badge bg-success">Baik</span>
                                @elseif($unitAlat->kondisi_saat_ini == 'rusak_ringan')
                                    <span class="badge bg-warning">Rusak Ringan</span>
                                @else
                                    <span class="badge bg-danger">Rusak Berat</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong></p>
                            <p>
                                @if($unitAlat->status == 'tersedia')
                                    <span class="badge bg-success">Tersedia</span>
                                @elseif($unitAlat->status == 'dipinjam')
                                    <span class="badge bg-warning">Terpinjam</span>
                                @elseif($unitAlat->status == 'rusak')
                                    <span class="badge bg-danger">Rusak</span>
                                @else
                                    <span class="badge bg-info">Maintenance</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @can('update', $unitAlat)
                        <a href="{{ route('unit-alat.edit', $unitAlat) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('unit-alat.qr', $unitAlat) }}" class="btn btn-info" target="_blank">
                            <i class="fas fa-qrcode me-1"></i>Cetak QR
                        </a>
                        @endcan
                        @can('delete', $unitAlat)
                        <form action="{{ route('unit-alat.destroy', $unitAlat) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endcan
                        <a href="{{ route('unit-alat.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">Peminjaman ({{ $unitAlat->peminjamanAlat->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($unitAlat->peminjamanAlat()->latest()->limit(5)->get() as $peminjaman)
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

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Pemeliharaan ({{ $unitAlat->pemeliharaanAlat->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($unitAlat->pemeliharaanAlat()->latest()->limit(5)->get() as $pemeliharaan)
                        <div class="mb-2 pb-2 border-bottom">
                            <small class="text-muted">{{ $pemeliharaan->tanggal_cek?->format('d/m/Y') ?? '-' }}</small><br>
                            <small>{{ $pemeliharaan->catatan ?? '-' }}</small><br>
                            <small class="badge bg-secondary">{{ $pemeliharaan->tanggal_cek ? 'Selesai' : 'Terjadwal' }}</small>
                        </div>
                    @empty
                        <p class="text-muted text-center">Belum ada pemeliharaan</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
