@extends('layouts.admin')

@section('title', 'Detail Peminjaman')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}">Data Peminjaman</a></li>
            <li class="breadcrumb-item active">Detail Peminjaman</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        {{ $peminjaman->alat?->nama_alat ?? $peminjaman->unitAlat?->alat?->nama_alat ?? 'Unknown' }}
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Peminjam:</strong></p>
                            <p>{{ $peminjaman->userPeminjam->nama }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Keperluan:</strong></p>
                            <p>{{ $peminjaman->keperluan }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Waktu Peminjaman:</strong></p>
                            <p>{{ $peminjaman->waktu_peminjaman->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Waktu Pengembalian (Rencana):</strong></p>
                            <p>{{ $peminjaman->waktu_pengembalian->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kondisi Saat Peminjaman:</strong></p>
                            <p><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $peminjaman->kondisi_saat_peminjaman)) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong></p>
                            <p>
                                <span class="badge bg-{{ $peminjaman->status == 'terpinjam' ? 'warning' : 'success' }}">
                                    {{ ucfirst(str_replace('_', ' ', $peminjaman->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($peminjaman->waktu_kembali_aktual)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Waktu Kembali Aktual:</strong></p>
                                <p>{{ $peminjaman->waktu_kembali_aktual->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Kondisi Saat Pengembalian:</strong></p>
                                <p><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $peminjaman->kondisi_saat_pengembalian)) }}</span></p>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        @if($peminjaman->status == 'terpinjam')
                            <a href="{{ route('peminjaman.return', $peminjaman) }}" class="btn btn-success">
                                <i class="fas fa-undo"></i> Kembalikan
                            </a>
                        @endif
                        <a href="{{ route('peminjaman.edit', $peminjaman) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('peminjaman.destroy', $peminjaman) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Status Peminjaman</h6>
                </div>
                <div class="card-body">
                    <p><strong>Tipe Alat:</strong><br><span class="badge bg-secondary">{{ $peminjaman->id_alat ? 'Agregat' : 'Unit' }}</span></p>
                    @if($peminjaman->isOverdue())
                        <p><strong>Status:</strong><br><span class="badge bg-danger">Overdue {{ $peminjaman->getDaysOverdue() }} hari</span></p>
                    @else
                        <p><strong>Status:</strong><br><span class="badge bg-success">Normal</span></p>
                    @endif
                    @if($peminjaman->id_unit_alat)
                        <p><strong>Kode Inventaris:</strong><br>{{ $peminjaman->unitAlat->kode_inventaris }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
