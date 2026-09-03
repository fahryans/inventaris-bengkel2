@extends('layouts.admin')

@section('title', 'Detail Pengadaan Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pengadaan_alat.index') }}">Data Pengadaan Alat</a></li>
            <li class="breadcrumb-item active">Detail Pengadaan</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $pengadaan->alat->nama_alat ?? 'Alat tidak ditemukan' }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Supplier:</strong></p>
                            <p>{{ $pengadaan->supplier }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Input oleh:</strong></p>
                            <p>{{ $pengadaan->userInput->nama ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Tanggal Pengadaan:</strong></p>
                            <p>{{ $pengadaan->tanggal_pengadaan?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal Masuk:</strong></p>
                            <p>{{ $pengadaan->tanggal_masuk?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Jumlah:</strong></p>
                            <p>{{ $pengadaan->jumlah }} unit</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Harga Perolehan/Unit:</strong></p>
                            <p>Rp {{ number_format($pengadaan->harga_perolehan, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p><strong>Total Harga:</strong></p>
                        <p><strong>Rp {{ number_format($pengadaan->harga_perolehan * $pengadaan->jumlah, 0, ',', '.') }}</strong></p>
                    </div>

                    @if($pengadaan->foto_transaksi)
                        <div class="mb-3">
                            <p><strong>Foto Transaksi:</strong></p>
                            <img src="{{ asset('storage/' . $pengadaan->foto_transaksi) }}" alt="Foto Transaksi" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        @can('update', $pengadaan)
                        @if(!$pengadaan->tanggal_masuk)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#receiveModal">
                            <i class="fas fa-box-open"></i> Terima Barang
                        </button>
                        @endif
                        <a href="{{ route('pengadaan_alat.edit', $pengadaan) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        @can('delete', $pengadaan)
                        <form action="{{ route('pengadaan_alat.destroy', $pengadaan) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endcan
                        <a href="{{ route('pengadaan_alat.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Informasi Alat</h6>
                </div>
                <div class="card-body">
                    <p><strong>Kategori:</strong><br><span class="badge bg-secondary">{{ $pengadaan->alat->kategori->nama_kategori ?? '-' }}</span></p>
                    <p><strong>Lab:</strong><br>{{ $pengadaan->alat->laboratorium->nama_labor ?? '-' }}</p>
                    <p><strong>Merek:</strong><br>{{ $pengadaan->merek ?? '-' }}</p>
                    <p><strong>Tipe Pelacakan:</strong><br><span class="badge bg-{{ ($pengadaan->alat->tipe_pelacakan ?? '') == 'unit' ? 'warning' : 'success' }}">{{ ucfirst($pengadaan->alat->tipe_pelacakan ?? '-') }}</span></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receiveModal" tabindex="-1" aria-labelledby="receiveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pengadaan_alat.mark_received', $pengadaan) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="receiveModalLabel">Terima Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tanggal_masuk" class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Konfirmasi Terima</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($pengadaan->foto_transaksi)
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-image me-1"></i> Foto Transaksi</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $pengadaan->foto_transaksi) }}" alt="Foto Transaksi"
                     class="img-fluid rounded shadow-lg" style="max-width: 100%;">
            </div>
        </div>
    </div>
</div>
@endif
@endsection
