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
                <div class="card-header bg-[#5b202f] text-[#f5f0e9]">
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
                            <p><strong>Spesifikasi:</strong></p>
                            <p>{{ $bahan->spesifikasi ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Satuan:</strong></p>
                            <p>{{ $bahan->satuan }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Total Stok:</strong></p>
                            <p><strong class="text-success">{{ $bahan->getTotalStock() }} {{ $bahan->satuan }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Pengadaan:</strong></p>
                            <p>{{ $bahan->getTotalAcquired() }} {{ $bahan->satuan }}</p>
                        </div>
                    </div>

                    @if($bahan->foto)
                        <div class="mb-3">
                            <p><strong>Foto:</strong></p>
                            <img src="{{ asset('storage/' . $bahan->foto) }}" alt="Foto Bahan" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        @can('update', $bahan)
                        <a href="{{ route('bahan.edit', $bahan) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        @can('delete', $bahan)
                        <form action="{{ route('bahan.destroy', $bahan) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endcan
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
                            <small><strong>Merek:</strong> {{ $pengadaan->merek }}</small><br>
                            <small><strong>Jumlah:</strong> {{ $pengadaan->jumlah }} {{ $bahan->satuan }}</small><br>
                            <small class="text-muted"><strong>Supplier:</strong> {{ $pengadaan->supplier }}</small>
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
