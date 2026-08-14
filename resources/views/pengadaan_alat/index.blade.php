@extends('layouts.admin')

@section('title', 'Data Pengadaan Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Pengadaan Alat</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pengadaan Alat</h5>
            @can('create', App\Models\PengadaanAlat::class)
            <a href="{{ route('pengadaan_alat.create') }}" class="btn btn-sm btn-light">
                <i class="fas fa-plus"></i> Tambah Pengadaan
            </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('pengadaan_alat.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari supplier..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" action="{{ route('pengadaan_alat.index') }}" class="d-flex gap-2">
                        <select name="alat" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Alat</option>
                            @foreach($alats as $alat)
                                <option value="{{ $alat->id }}" {{ request('alat') == $alat->id ? 'selected' : '' }}>
                                    {{ $alat->nama_alat }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Alat</th>
                            <th>Supplier</th>
                            <th>Tanggal Pengadaan</th>
                            <th>Tanggal Masuk</th>
                            <th>Jumlah</th>
                            <th>Harga/Unit</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengadaans as $pad)
                            <tr>
                                <td><strong>{{ $pad->alat->nama_alat ?? '-' }}</strong></td>
                                <td>{{ $pad->supplier }}</td>
                                <td>{{ $pad->tanggal_pengadaan?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $pad->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $pad->jumlah }}</td>
                                <td>Rp {{ number_format($pad->harga_perolehan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($pad->harga_perolehan * $pad->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('pengadaan_alat.show', $pad) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $pad)
                                        <a href="{{ route('pengadaan_alat.edit', $pad) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $pad)
                                        <form action="{{ route('pengadaan_alat.destroy', $pad) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus" 
                                                    onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i> Tidak ada data pengadaan alat
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $pengadaans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
