@extends('layouts.admin')

@section('title', 'Data Pemakaian Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Pemakaian Bahan</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9] d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pemakaian Bahan</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('export', 'pemakaian_bahan') }}?format=pdf"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('export', 'pemakaian_bahan') }}?format=excel"><i class="fas fa-file-excel text-success me-2"></i>Excel</a></li>
                    </ul>
                </div>
                @can('create', App\Models\PemakaianBahan::class)
                <a href="{{ route('pemakaian_bahan.create') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-plus"></i> Tambah Pemakaian
                </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('pemakaian_bahan.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari keperluan..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('pemakaian_bahan.index') }}" class="d-flex gap-2">
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
                <div class="col-md-2">
                    <form method="GET" action="{{ route('pemakaian_bahan.index') }}" class="d-flex gap-2">
                        <select name="verified" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('verified') == '1' ? 'selected' : '' }}>Terverifikasi</option>
                            <option value="0" {{ request('verified') == '0' ? 'selected' : '' }}>Belum Verifikasi</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Bahan</th>
                            <th>Keperluan</th>
                            <th>Waktu Pemakaian</th>
                            <th>Pengambilan</th>
                            <th>Terpakai</th>
                            <th>Pengembalian</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pemakaians as $pem)
                            <tr>
                                <td><strong>{{ $pem->bahan->nama_bahan ?? '-' }}</strong></td>
                                <td>{{ $pem->keperluan }}</td>
                                <td>{{ $pem->waktu_pemakaian?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $pem->jumlah_pengambilan }}</td>
                                <td>{{ $pem->jumlah_terpakai }}</td>
                                <td>{{ $pem->jumlah_pengembalian }}</td>
                                <td>
                                    @if($pem->id_user_verifikasi)
                                        <span class="badge bg-success">Terverifikasi</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('pemakaian_bahan.show', $pem) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $pem)
                                        <a href="{{ route('pemakaian_bahan.edit', $pem) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $pem)
                                        <form action="{{ route('pemakaian_bahan.destroy', $pem) }}" method="POST" style="display:inline;">
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
                                    <i class="fas fa-inbox"></i> Tidak ada data pemakaian bahan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $pemakaians->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
