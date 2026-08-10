@extends('layouts.admin')

@section('title', 'Detail Pemakaian Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pemakaian_bahan.index') }}">Data Pemakaian Bahan</a></li>
            <li class="breadcrumb-item active">Detail Pemakaian</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $pemakaian->bahan->nama_bahan }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Keperluan:</strong></p>
                            <p>{{ $pemakaian->keperluan }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Waktu Pemakaian:</strong></p>
                            <p>{{ $pemakaian->waktu_pemakaian->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p><strong>Jumlah Pengambilan:</strong></p>
                            <p>{{ $pemakaian->jumlah_pengambilan }} {{ $pemakaian->bahan->satuan }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Jumlah Terpakai:</strong></p>
                            <p>{{ $pemakaian->jumlah_terpakai }} {{ $pemakaian->bahan->satuan }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Jumlah Pengembalian:</strong></p>
                            <p>{{ $pemakaian->jumlah_pengembalian }} {{ $pemakaian->bahan->satuan }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Batch Pengadaan:</strong></p>
                            <p>{{ $pemakaian->pengadaanBahan->supplier }} ({{ $pemakaian->pengadaanBahan->tanggal_pengadaan->format('d/m/Y') }})</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status Verifikasi:</strong></p>
                            <p>
                                @if($pemakaian->id_user_verifikasi)
                                    <span class="badge bg-success">Terverifikasi oleh {{ $pemakaian->userVerifikasi->nama }}</span>
                                @else
                                    <span class="badge bg-warning">Belum Diverifikasi</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @if(!$pemakaian->id_user_verifikasi)
                            <form action="{{ route('pemakaian_bahan.verify', $pemakaian) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Verifikasi
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('pemakaian_bahan.edit', $pemakaian) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('pemakaian_bahan.destroy', $pemakaian) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        <a href="{{ route('pemakaian_bahan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Informasi Bahan</h6>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong><br>{{ $pemakaian->bahan->nama_bahan }}</p>
                    <p><strong>Kategori:</strong><br><span class="badge bg-secondary">{{ $pemakaian->bahan->kategori->nama_kategori }}</span></p>
                    <p><strong>Lab:</strong><br>{{ $pemakaian->bahan->laboratorium->nama_labor }}</p>
                    <p><strong>Merek:</strong><br>{{ $pemakaian->bahan->merek ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
