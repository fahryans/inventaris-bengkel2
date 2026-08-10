@extends('layouts.admin')

@section('title', 'Detail Pemeliharaan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pemeliharaan.index') }}">Data Pemeliharaan</a></li>
            <li class="breadcrumb-item active">Detail Pemeliharaan</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $pemeliharaan->unitAlat->kode_inventaris }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Alat:</strong></p>
                            <p>{{ $pemeliharaan->unitAlat->alat->nama_alat }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Teknisi:</strong></p>
                            <p>{{ $pemeliharaan->teknisi->nama }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Tanggal Cek:</strong></p>
                            <p>{{ $pemeliharaan->tanggal_cek->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal Cek Berikutnya:</strong></p>
                            <p>{{ $pemeliharaan->tanggal_cek_berikutnya->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kondisi:</strong></p>
                            <p><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $pemeliharaan->kondisi)) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Biaya:</strong></p>
                            <p>Rp {{ number_format($pemeliharaan->biaya ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p><strong>Detail Biaya:</strong></p>
                        <p>{{ $pemeliharaan->detail_biaya ?? '-' }}</p>
                    </div>

                    <div class="mb-3">
                        <p><strong>Catatan:</strong></p>
                        <p>{{ $pemeliharaan->catatan ?? '-' }}</p>
                    </div>

                    <div class="mb-3">
                        <p><strong>Hasil Pemeliharaan:</strong></p>
                        <p>{{ $pemeliharaan->hasil_pemeliharaan ?? '-' }}</p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('pemeliharaan.edit', $pemeliharaan) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('pemeliharaan.destroy', $pemeliharaan) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        <a href="{{ route('pemeliharaan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Informasi Unit</h6>
                </div>
                <div class="card-body">
                    <p><strong>Kode Inventaris:</strong><br>{{ $pemeliharaan->unitAlat->kode_inventaris }}</p>
                    <p><strong>Kondisi Saat Ini:</strong><br><span class="badge bg-warning">{{ ucfirst($pemeliharaan->unitAlat->kondisi_saat_ini) }}</span></p>
                    <p><strong>Status:</strong><br><span class="badge bg-{{ $pemeliharaan->unitAlat->status == 'tersedia' ? 'success' : 'danger' }}">{{ ucfirst($pemeliharaan->unitAlat->status) }}</span></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
