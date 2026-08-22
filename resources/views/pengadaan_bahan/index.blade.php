@extends('layouts.admin')

@section('title', 'Data Pengadaan Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Pengadaan Bahan</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pengadaan Bahan</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('export', 'pengadaan_bahan') }}?format=pdf"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('export', 'pengadaan_bahan') }}?format=excel"><i class="fas fa-file-excel text-success me-2"></i>Excel</a></li>
                    </ul>
                </div>
                @can('create', App\Models\PengadaanBahan::class)
                <a href="{{ route('pengadaan_bahan.create') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-plus"></i> Tambah Pengadaan
                </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('pengadaan_bahan.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari supplier..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" action="{{ route('pengadaan_bahan.index') }}" class="d-flex gap-2">
                        <select name="bahan" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Bahan</option>
                            @foreach($bahans as $bahan)
                                <option value="{{ $bahan->id }}" {{ request('bahan') == $bahan->id ? 'selected' : '' }}>
                                    {{ $bahan->nama_bahan }}
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
                            <th>Bahan</th>
                            <th>Supplier</th>
                            <th>Tanggal Pengadaan</th>
                            <th>Tanggal Masuk</th>
                            <th>Jumlah</th>
                            <th>Harga/Unit</th>
                            <th>Total</th>
                            <th>Expire</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengadaans as $pad)
                            <tr>
                                <td><strong>{{ $pad->bahan->nama_bahan ?? '-' }}</strong></td>
                                <td>{{ $pad->supplier }}</td>
                                <td>{{ $pad->tanggal_pengadaan?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $pad->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $pad->jumlah }}</td>
                                <td>Rp {{ number_format($pad->harga_perolehan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($pad->harga_perolehan * $pad->jumlah, 0, ',', '.') }}</td>
                                <td>{{ $pad->masa_expire_bahan?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('pengadaan_bahan.show', $pad) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $pad)
                                        <a href="{{ route('pengadaan_bahan.edit', $pad) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $pad)
                                        <form action="{{ route('pengadaan_bahan.destroy', $pad) }}" method="POST" style="display:inline;">
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
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i> Tidak ada data pengadaan bahan
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
