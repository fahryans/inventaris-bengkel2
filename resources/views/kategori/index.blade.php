@extends('layouts.admin')

@section('title', 'Data Kategori')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Kategori</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Kategori</h5>
            @can('create', \App\Models\Kategori::class)
            <a href="{{ route('kategori.create') }}" class="btn btn-sm btn-light">
                <i class="fas fa-plus"></i> Tambah Kategori
            </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('kategori.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari nama..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('kategori.index') }}" class="d-flex gap-2">
                        <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Jenis</option>
                            <option value="alat" {{ request('jenis') == 'alat' ? 'selected' : '' }}>Alat</option>
                            <option value="bahan" {{ request('jenis') == 'bahan' ? 'selected' : '' }}>Bahan</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Jenis</th>
                            <th>Total Item</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoris as $kat)
                            <tr>
                                <td>
                                    <strong>{{ $kat->nama_kategori }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $kat->jenis == 'alat' ? 'success' : 'info' }}">
                                        {{ ucfirst($kat->jenis) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $kat->jenis == 'alat' ? $kat->alat->count() : $kat->bahan->count() }}
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('kategori.show', $kat) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $kat)
                                        <a href="{{ route('kategori.edit', $kat) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $kat)
                                        <form action="{{ route('kategori.destroy', $kat) }}" method="POST" style="display:inline;">
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
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i> Tidak ada data kategori
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $kategoris->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
