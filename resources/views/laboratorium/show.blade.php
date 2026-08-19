@extends('layouts.admin')

@section('title', 'Detail Laboratorium')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('laboratorium.index') }}">Data Laboratorium</a></li>
            <li class="breadcrumb-item active">Detail Lab</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
<div class="card-header bg-[#5b202f] text-[#f5f0e9]">
                <h5 class="mb-0">{{ $laboratorium->nama_labor }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kepala Lab:</strong></p>
                            <p>{{ $laboratorium->kalab->nama ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email Kepala Lab:</strong></p>
                            <p>{{ $laboratorium->kalab->email ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p><strong>Lokasi:</strong></p>
                        <p>{{ $laboratorium->lokasi ?? '-' }}</p>
                    </div>

                    <div class="mb-3">
                        <p><strong>SOP:</strong></p>
                        <p>{{ $laboratorium->sop ?? '-' }}</p>
                    </div>

                    <div class="d-flex gap-2">
                        @can('update', $laboratorium)
                        <a href="{{ route('laboratorium.edit', $laboratorium) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        @can('delete', $laboratorium)
                        <form action="{{ route('laboratorium.destroy', $laboratorium) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endcan
                        <a href="{{ route('laboratorium.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Alat ({{ $laboratorium->alat->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($laboratorium->alat()->limit(10)->get() as $alat)
                        <div class="mb-2 pb-2 border-bottom">
                            <p class="mb-1"><strong>{{ $alat->nama_alat }}</strong></p>
                            <small class="text-muted">{{ $alat->kategori->nama_kategori }}</small><br>
                            <small>Jumlah: {{ $alat->jumlah_alat }}</small>
                        </div>
                    @empty
                        <p class="text-muted text-center">Belum ada alat</p>
                    @endforelse
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">Bahan ({{ $laboratorium->bahan->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($laboratorium->bahan()->limit(10)->get() as $bahan)
                        <div class="mb-2 pb-2 border-bottom">
                            <p class="mb-1"><strong>{{ $bahan->nama_bahan }}</strong></p>
                            <small class="text-muted">{{ $bahan->kategori->nama_kategori }}</small><br>
                            <small>Stok: {{ $bahan->stok_saat_ini }} {{ $bahan->satuan }}</small>
                        </div>
                    @empty
                        <p class="text-muted text-center">Belum ada bahan</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
