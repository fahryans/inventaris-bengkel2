@extends('layouts.admin')

@section('title', 'Detail Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bahan.index') }}">Data Bahan</a></li>
            <li class="breadcrumb-item active">Detail Bahan</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $bahan->nama_bahan }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kategori:</strong></p>
                            <p><span class="badge bg-info">{{ $bahan->kategori->nama_kategori }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Laboratorium:</strong></p>
                            <p>{{ $bahan->laboratorium->nama_labor }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Merek:</strong></p>
                            <p>{{ $bahan->merek ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Spesifikasi:</strong></p>
                            <p>{{ $bahan->spesifikasi ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p><strong>Stok Saat Ini:</strong></p>
                            <p><strong>{{ $bahan->stok_saat_ini }} {{ $bahan->satuan }}</strong></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Stok Minimum:</strong></p>
                            <p>{{ $bahan->stok_minimum }} {{ $bahan->satuan }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Status Stok:</strong></p>
                            <p>
                                @if($bahan->isStokMenipis())
                                    <span class="badge bg-danger">Menipis</span>
                                @else
                                    <span class="badge bg-success">Normal</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($bahan->foto)
                        <div class="mb-3">
                            <p><strong>Foto:</strong></p>
                            <img src="{{ asset('storage/' . $bahan->foto) }}" alt="Foto Bahan" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('bahan.edit', $bahan) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('bahan.destroy', $bahan) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        <a href="{{ route('bahan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Pengadaan ({{ $bahan->pengadaanBahan->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($bahan->pengadaanBahan()->latest()->limit(5)->get() as $pengadaan)
                        <div class="mb-2 pb-2 border-bottom">
                            <small class="text-muted">{{ $pengadaan->tanggal_pengadaan->format('d/m/Y') }}</small><br>
                            <small>Jumlah: {{ $pengadaan->jumlah }} {{ $bahan->satuan }}</small><br>
                            <small class="text-muted">{{ $pengadaan->supplier }}</small>
                        </div>
                    @empty
                        <p class="text-muted text-center">Belum ada pengadaan</p>
                    @endforelse
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">Pemakaian ({{ $bahan->pemakaianBahan->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($bahan->pemakaianBahan()->latest()->limit(5)->get() as $pemakaian)
                        <div class="mb-2 pb-2 border-bottom">
                            <small class="text-muted">{{ $pemakaian->waktu_pemakaian->format('d/m/Y') }}</small><br>
                            <small>Dipakai: {{ $pemakaian->jumlah_terpakai }} {{ $bahan->satuan }}</small><br>
                            <small class="text-muted">{{ $pemakaian->keperluan }}</small>
                        </div>
                    @empty
                        <p class="text-muted text-center">Belum ada pemakaian</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
