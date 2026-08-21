@extends('layouts.admin')

@section('title', 'Data Unit Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Unit Alat</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Daftar Unit Alat</h5>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <form method="GET" action="{{ route('unit-alat.index') }}" class="d-flex gap-2">
<input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Cari kode inventaris atau nama alat..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" action="{{ route('unit-alat.index') }}" class="d-flex gap-2">
                        <select name="id_alat" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Alat</option>
                            @foreach($alats as $alat)
                                <option value="{{ $alat->id }}" {{ request('id_alat') == $alat->id ? 'selected' : '' }}>
                                    {{ $alat->nama_alat }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('unit-alat.index') }}" class="d-flex gap-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                            <option value="rusak" {{ request('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('unit-alat.index') }}" class="d-flex gap-2">
                        <select name="kondisi" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Kondisi</option>
                            <option value="baik" {{ request('kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                            <option value="rusak_ringan" {{ request('kondisi') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="rusak_berat" {{ request('kondisi') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('unit-alat.index') }}">
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="kode_inventaris" {{ request('sort') == 'kode_inventaris' ? 'selected' : '' }}>Kode A-Z</option>
                            <option value="kode_inventaris|desc" {{ request('sort') == 'kode_inventaris|desc' ? 'selected' : '' }}>Kode Z-A</option>
                            <option value="alat" {{ request('sort') == 'alat' ? 'selected' : '' }}>Nama Alat A-Z</option>
                            <option value="alat|desc" {{ request('sort') == 'alat|desc' ? 'selected' : '' }}>Nama Alat Z-A</option>
                            <option value="spesifikasi" {{ request('sort') == 'spesifikasi' ? 'selected' : '' }}>Spesifikasi A-Z</option>
                            <option value="spesifikasi|desc" {{ request('sort') == 'spesifikasi|desc' ? 'selected' : '' }}>Spesifikasi Z-A</option>
                            <option value="created_at|desc" {{ request('sort') == 'created_at|desc' ? 'selected' : '' }}>Terbaru</option>
                        </select>
                        @foreach(['id_alat' => request('id_alat'), 'status' => request('status'), 'kondisi' => request('kondisi'), 'search' => request('search')] as $key => $val)
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
                            <th>Kode Inventaris</th>
                            <th>Nama Alat</th>
                            <th>Spesifikasi</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unitAlats as $unit)
                            <tr>
                                <td>
                                    <strong>{{ $unit->kode_inventaris }}</strong>
                                </td>
                                <td>{{ $unit->alat->nama_alat }}</td>
                                <td>
                                    @if($unit->spesifikasiAlat)
                                        <span class="badge bg-secondary">{{ $unit->spesifikasiAlat->kode_spesifikasi }}</span>
                                        <br><small>{{ $unit->spesifikasiAlat->nama_spesifikasi }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($unit->kondisi_saat_ini == 'baik')
                                        <span class="badge bg-success">Baik</span>
                                    @elseif($unit->kondisi_saat_ini == 'rusak_ringan')
                                        <span class="badge bg-warning">Rusak Ringan</span>
                                    @else
                                        <span class="badge bg-danger">Rusak Berat</span>
                                    @endif
                                </td>
                                <td>
                                    @if($unit->status == 'tersedia')
                                        <span class="badge bg-success">Tersedia</span>
                                    @elseif($unit->status == 'dipinjam')
                                        <span class="badge bg-warning">Terpinjam</span>
                                    @elseif($unit->status == 'rusak')
                                        <span class="badge bg-danger">Rusak</span>
                                    @else
                                        <span class="badge bg-info">Maintenance</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('unit-alat.show', $unit) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('unit-alat.qr', $unit) }}" class="btn btn-outline-info" title="Cetak QR" target="_blank">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                        @can('update', $unit)
                                        <a href="{{ route('unit-alat.edit', $unit) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $unit)
                                        <form action="{{ route('unit-alat.destroy', $unit) }}" method="POST" style="display:inline;">
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
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i> Tidak ada data unit alat
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $unitAlats->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
