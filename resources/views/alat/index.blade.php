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
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('export', 'alat') }}?format=pdf"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('export', 'alat') }}?format=excel"><i class="fas fa-file-excel text-success me-2"></i>Excel</a></li>
                    </ul>
                </div>
                @can('create', App\Models\Alat::class)
                <a href="{{ route('alat.create') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-plus"></i> Tambah Alat
                </a>
                @endcan
            </div>
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
                <div class="col-md-2">
                    <form method="GET" action="{{ route('alat.index') }}">
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="nama_alat" {{ request('sort') == 'nama_alat' ? 'selected' : '' }}>Nama A-Z</option>
                            <option value="nama_alat|desc" {{ request('sort') == 'nama_alat|desc' ? 'selected' : '' }}>Nama Z-A</option>
                            <option value="created_at|desc" {{ request('sort') == 'created_at|desc' ? 'selected' : '' }}>Terbaru</option>
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Terlama</option>
                        </select>
                        @foreach(['kategori' => request('kategori'), 'labor' => request('labor'), 'tipe_pelacakan' => request('tipe_pelacakan'), 'search' => request('search')] as $key => $val)
                            @if($val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endif
                        @endforeach
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
                            <th>Tipe</th>
                            <th>Spesifikasi</th>
                            <th>Total</th>
                            <th>Terpinjam</th>
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
                                <td>
                                    <span class="badge bg-{{ $alat->tipe_pelacakan == 'unit' ? 'warning' : 'success' }}">
                                        {{ ucfirst($alat->tipe_pelacakan) }}
                                    </span>
                                </td>
                                <td>
                                    @forelse($alat->spesifikasiAlat as $spesifikasi)
                                        <span class="badge bg-secondary">{{ $spesifikasi->kode_spesifikasi }}</span>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($alat->tipe_pelacakan === 'unit')
                                        <strong>{{ $alat->unit_alat_count }}</strong>
                                    @else
                                        <strong>{{ $alat->pengadaan_alat_sum_jumlah ?? 0 }}</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($alat->tipe_pelacakan === 'unit')
                                        <span class="badge bg-warning">{{ $alat->unit_alat_pinjam }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ $alat->peminjaman_alat_sum_jumlah ?? 0 }}</span>
                                    @endif
                                </td>
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
