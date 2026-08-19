@extends('layouts.admin')

@section('title', 'Data Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Bahan</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Bahan</h5>
            @can('create', App\Models\Bahan::class)
            <a href="{{ route('bahan.create') }}" class="btn btn-sm btn-light">
                <i class="fas fa-plus"></i> Tambah Bahan
            </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('bahan.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari nama bahan..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('bahan.index') }}" class="d-flex gap-2">
                        <select name="kategori" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('bahan.index') }}" class="d-flex gap-2">
                        <select name="labor" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Lab</option>
                            @foreach($laboratoriums as $lab)
                                <option value="{{ $lab->id }}" {{ request('labor') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->nama_labor }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('bahan.index') }}" class="d-flex gap-2">
                        <select name="stock_status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Stok</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Menipis</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th>Lab</th>
                            <th>Stok Saat Ini</th>
                            <th>Stok Minimum</th>
                            <th>Satuan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahans as $bahan)
                            <tr class="{{ $bahan->isStokMenipis() ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $bahan->nama_bahan }}</strong>
                                </td>
                                <td><span class="badge bg-info">{{ $bahan->kategori->nama_kategori }}</span></td>
                                <td>{{ $bahan->laboratorium->nama_labor }}</td>
                                <td>{{ $bahan->stok_saat_ini }}</td>
                                <td>{{ $bahan->stok_minimum }}</td>
                                <td>{{ $bahan->satuan }}</td>
                                <td>
                                    @if($bahan->isStokMenipis())
                                        <span class="badge bg-danger">Menipis</span>
                                    @else
                                        <span class="badge bg-success">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('bahan.show', $bahan) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $bahan)
                                        <a href="{{ route('bahan.edit', $bahan) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $bahan)
                                        <form action="{{ route('bahan.destroy', $bahan) }}" method="POST" style="display:inline;">
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
                                    <i class="fas fa-inbox"></i> Tidak ada data bahan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $bahans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
