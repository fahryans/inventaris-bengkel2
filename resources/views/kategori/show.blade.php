@extends('layouts.admin')

@section('title', 'Detail Kategori')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Data Kategori</a></li>
            <li class="breadcrumb-item active">Detail Kategori</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-[#5b202f] text-[#f5f0e9]">
                    <h5 class="mb-0">{{ $kategori->nama_kategori }}</h5>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <p><strong>Jenis:</strong></p>
                        <p><span class="badge bg-{{ $kategori->jenis == 'alat' ? 'success' : 'info' }}">{{ ucfirst($kategori->jenis) }}</span></p>
                    </div>

                    <div class="d-flex gap-2">
                        @can('update', $kategori)
                        <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        @can('delete', $kategori)
                        <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endcan
                        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($kategori->jenis == 'alat')
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Alat ({{ $kategori->alat->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @forelse($kategori->alat()->limit(10)->get() as $alat)
                            <div class="mb-2 pb-2 border-bottom">
                                <p class="mb-1"><strong>{{ $alat->nama_alat }}</strong></p>
                                <small class="text-muted">Lab: {{ $alat->laboratorium->nama_labor }}</small><br>
                                <small>Jumlah: {{ $alat->tipe_pelacakan === 'unit' ? $alat->unit_alat_count : max(0, ($alat->pengadaan_alat_sum_jumlah ?? 0) - ($alat->peminjaman_alat_sum_jumlah ?? 0)) }}</small>
                            </div>
                        @empty
                            <p class="text-muted text-center">Belum ada alat</p>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Bahan ({{ $kategori->bahan->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @forelse($kategori->bahan()->limit(10)->get() as $bahan)
                            <div class="mb-2 pb-2 border-bottom">
                                <p class="mb-1"><strong>{{ $bahan->nama_bahan }}</strong></p>
                                <small class="text-muted">Lab: {{ $bahan->laboratorium->nama_labor }}</small><br>
                                <small>Stok: {{ $bahan->stok_saat_ini }} {{ $bahan->satuan }}</small>
                            </div>
                        @empty
                            <p class="text-muted text-center">Belum ada bahan</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
