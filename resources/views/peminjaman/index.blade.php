@extends('layouts.admin')

@section('title', 'Data Peminjaman')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Peminjaman</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Peminjaman Alat</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('export', 'peminjaman') }}?format=pdf"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('export', 'peminjaman') }}?format=excel"><i class="fas fa-file-excel text-success me-2"></i>Excel</a></li>
                    </ul>
                </div>
                @can('create', \App\Models\PeminjamanAlat::class)
                @if(!in_array(Auth::user()->role, ['admin_jurusan', 'kepala_labor', 'teknisi', 'kadep']))
                <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-plus"></i> Tambah Peminjaman
                </a>
                @endif
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('peminjaman.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari keperluan..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('peminjaman.index') }}" class="d-flex gap-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="terpinjam" {{ request('status') == 'terpinjam' ? 'selected' : '' }}>Terpinjam</option>
                            <option value="sudah_dikembalikan" {{ request('status') == 'sudah_dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Alat</th>
                            <th>Jumlah</th>
                            <th>Peminjam</th>
                            <th>Keperluan</th>
                            <th>Waktu Peminjaman</th>
                            <th>Waktu Pengembalian</th>
                            <th>Status</th>
                            <th>Overdue</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjaman as $pem)
                            <tr class="{{ $pem->isOverdue() ? 'table-danger' : '' }}">
                                <td>
                                    <strong>
                                        {{ $pem->alat?->nama_alat ?? $pem->unitAlat?->alat?->nama_alat ?? 'Unknown' }}
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $pem->jumlah }}</span>
                                </td>
                                <td>{{ $pem->userPeminjam->nama }}</td>
                                <td>{{ $pem->keperluan }}</td>
                                <td>{{ $pem->waktu_peminjaman->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($pem->waktu_pengembalian)
                                        @php
                                            $isReturned = $pem->status === 'sudah_dikembalikan';
                                            $isOverdue = !$isReturned && $pem->waktu_pengembalian < now();
                                            $returnedLate = $isReturned && $pem->waktu_kembali_aktual && $pem->waktu_kembali_aktual > $pem->waktu_pengembalian;
                                        @endphp
                                        <small class="{{ $returnedLate ? 'text-warning fw-bold' : ($isReturned ? 'text-success' : ($isOverdue ? 'text-danger fw-bold' : 'text-muted')) }}">
                                            <i class="fas fa-{{ $returnedLate ? 'exclamation-circle' : ($isReturned ? 'check-circle' : ($isOverdue ? 'exclamation-triangle' : 'clock')) }}"></i>
                                            {{ $pem->waktu_pengembalian->format('d/m/Y H:i') }}
                                            @if($returnedLate) (terlambat dikembalikan) @elseif($isOverdue) (overdue) @endif
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $pem->status == 'terpinjam' ? 'warning' : 'success' }}">
                                        {{ ucfirst(str_replace('_', ' ', $pem->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($pem->isOverdue())
                                        <span class="badge bg-danger">{{ $pem->getDaysOverdue() }} hari</span>
                                    @else
                                        <span class="badge bg-success">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('peminjaman.show', $pem) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('delete', $pem)
                                        <form action="{{ route('peminjaman.destroy', $pem) }}" method="POST" style="display:inline;">
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
                                    <i class="fas fa-inbox"></i> Tidak ada data peminjaman
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $peminjaman->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
