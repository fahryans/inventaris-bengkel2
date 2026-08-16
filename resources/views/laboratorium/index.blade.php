@extends('layouts.admin')

@section('title', 'Data Laboratorium')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Laboratorium</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Daftar Laboratorium</h5>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('laboratorium.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari nama lab..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Laboratorium</th>
                            <th>Kepala Lab</th>
                            <th>Lokasi</th>
                            <th>Jumlah Alat</th>
                            <th>Jumlah Bahan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laboratoriums as $lab)
                            <tr>
                                <td>
                                    <strong>{{ $lab->nama_labor }}</strong>
                                </td>
                                <td>{{ $lab->kalab->nama ?? '-' }}</td>
                                <td>{{ $lab->lokasi ?? '-' }}</td>
                                <td>{{ $lab->alat->count() }}</td>
                                <td>{{ $lab->bahan->count() }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('laboratorium.show', $lab) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $lab)
                                        <a href="{{ route('laboratorium.edit', $lab) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $lab)
                                        <form action="{{ route('laboratorium.destroy', $lab) }}" method="POST" style="display:inline;">
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
                                    <i class="fas fa-inbox"></i> Tidak ada data laboratorium
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $laboratoriums->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
