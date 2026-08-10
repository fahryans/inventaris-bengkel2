@extends('layouts.admin')

@section('title', 'Data Pemeliharaan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Pemeliharaan</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pemeliharaan</h5>
            <a href="{{ route('pemeliharaan.create') }}" class="btn btn-sm btn-light">
                <i class="fas fa-plus"></i> Tambah Pemeliharaan
            </a>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('pemeliharaan.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari kode inventaris..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('pemeliharaan.index') }}" class="d-flex gap-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Akan Datang</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('pemeliharaan.index') }}" class="d-flex gap-2">
                        <select name="teknisi" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Teknisi</option>
                            @foreach($teknisis as $tek)
                                <option value="{{ $tek->id }}" {{ request('teknisi') == $tek->id ? 'selected' : '' }}>
                                    {{ $tek->nama }}
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
                            <th>Kode Inventaris</th>
                            <th>Alat</th>
                            <th>Teknisi</th>
                            <th>Tgl Cek</th>
                            <th>Tgl Cek Berikutnya</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pemeliharaans as $pm)
                            <tr class="{{ $pm->isOverdue() ? 'table-danger' : '' }}">
                                <td>
                                    <strong>{{ $pm->unitAlat->kode_inventaris }}</strong>
                                </td>
                                <td>{{ $pm->unitAlat->alat->nama_alat }}</td>
                                <td>{{ $pm->teknisi->nama }}</td>
                                <td>{{ $pm->tanggal_cek->format('d/m/Y') }}</td>
                                <td>{{ $pm->tanggal_cek_berikutnya->format('d/m/Y') }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($pm->kondisi) }}</span></td>
                                <td>
                                    @if($pm->isOverdue())
                                        <span class="badge bg-danger">Overdue</span>
                                    @else
                                        <span class="badge bg-success">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('pemeliharaan.show', $pm) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('pemeliharaan.edit', $pm) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('pemeliharaan.destroy', $pm) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus" 
                                                    onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i> Tidak ada data pemeliharaan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $pemeliharaans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
