@extends('layouts.admin')

@section('title', 'Data Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Alat</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9] d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Alat</h5>
            @can('create', App\Models\Alat::class)
            <a href="{{ route('alat.create') }}" class="btn btn-sm btn-light">
                <i class="fas fa-plus"></i> Tambah Alat
            </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('alat.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari nama alat..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('alat.index') }}" class="d-flex gap-2">
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
                    <form method="GET" action="{{ route('alat.index') }}" class="d-flex gap-2">
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
                    <form method="GET" action="{{ route('alat.index') }}" class="d-flex gap-2">
                        <select name="tipe_pelacakan" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Tipe</option>
                            <option value="agregat" {{ request('tipe_pelacakan') == 'agregat' ? 'selected' : '' }}>Agregat</option>
                            <option value="unit" {{ request('tipe_pelacakan') == 'unit' ? 'selected' : '' }}>Unit</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Alat</th>
                            <th>Kategori</th>
                            <th>Lab</th>
                            <th>Merek</th>
                            <th>Tipe Pelacakan</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alats as $alat)
                            <tr>
                                <td>
                                    <strong>{{ $alat->nama_alat }}</strong>
                                </td>
                                <td><span class="badge bg-info">{{ $alat->kategori->nama_kategori }}</span></td>
                                <td>{{ $alat->laboratorium->nama_labor }}</td>
                                <td>{{ $alat->merek ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $alat->tipe_pelacakan == 'unit' ? 'warning' : 'success' }}">
                                        {{ ucfirst($alat->tipe_pelacakan) }}
                                    </span>
                                </td>
                                <td>{{ $alat->jumlah_alat }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('alat.show', $alat) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $alat)
                                        <a href="{{ route('alat.edit', $alat) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $alat)
                                        <form action="{{ route('alat.destroy', $alat) }}" method="POST" style="display:inline;">
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i> Tidak ada data alat
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $alats->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
