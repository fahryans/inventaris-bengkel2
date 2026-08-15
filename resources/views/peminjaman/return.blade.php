@extends('layouts.admin')

@section('title', 'Kembalikan Peminjaman')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}">Data Peminjaman</a></li>
            <li class="breadcrumb-item"><a href="{{ route('peminjaman.show', $peminjaman) }}">Detail Peminjaman</a></li>
            <li class="breadcrumb-item active">Kembalikan</li>
        </ol>
    </nav>

    @can('return', $peminjaman)
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Pengembalian Alat</h5>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <strong>Alat:</strong> {{ $peminjaman->alat?->nama_alat ?? $peminjaman->unitAlat?->alat?->nama_alat ?? 'Unknown' }}<br>
                <strong>Peminjam:</strong> {{ $peminjaman->userPeminjam->nama }}<br>
                <strong>Waktu Peminjaman:</strong> {{ $peminjaman->waktu_peminjaman->format('d/m/Y H:i') }}
            </div>

            <form action="{{ route('peminjaman.return', $peminjaman) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="waktu_kembali_aktual" class="form-label">Waktu Kembali Aktual <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="waktu_kembali_aktual" id="waktu_kembali_aktual" class="form-control @error('waktu_kembali_aktual') is-invalid @enderror" 
                                   value="{{ old('waktu_kembali_aktual', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('waktu_kembali_aktual')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kondisi_saat_pengembalian" class="form-label">Kondisi Saat Pengembalian <span class="text-danger">*</span></label>
                            <select name="kondisi_saat_pengembalian" id="kondisi_saat_pengembalian" class="form-select @error('kondisi_saat_pengembalian') is-invalid @enderror" required>
                                <option value="">Pilih Kondisi</option>
                                <option value="baik" {{ old('kondisi_saat_pengembalian') == 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak_ringan" {{ old('kondisi_saat_pengembalian') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="rusak_berat" {{ old('kondisi_saat_pengembalian') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                            @error('kondisi_saat_pengembalian')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Konfirmasi Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> Anda tidak memiliki akses untuk mengembalikan peminjaman ini.
    </div>
    @endcan
</div>
@endsection
