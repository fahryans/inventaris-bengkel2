@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
            <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">{{ $title }}</h1>
                    <p class="text-muted">Detail data laporan.</p>
                </div>
                <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ $title }}</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            @if($tipe === 'alat')
                                <th>Nama Alat</th>
                                <th>Merek</th>
                                <th>Kategori</th>
                                <th>Laboratorium</th>
                                <th>Tipe Pelacakan</th>
                                <th>Jumlah</th>
                            @elseif($tipe === 'bahan')
                                <th>Nama Bahan</th>
                                <th>Kategori</th>
                                <th>Stok Saat Ini</th>
                                <th>Stok Minimum</th>
                                <th>Satuan</th>
                            @elseif($tipe === 'peminjaman')
                                <th>Alat</th>
                                <th>Unit</th>
                                <th>Peminjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                            @elseif($tipe === 'pemeliharaan')
                                <th>Unit Alat</th>
                                <th>Teknisi</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Status</th>
                            @elseif($tipe === 'pengadaan_alat')
                                <th>Alat</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            @elseif($tipe === 'pengadaan_bahan')
                                <th>Bahan</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            @elseif($tipe === 'pemakaian_bahan')
                                <th>Bahan</th>
                                <th>Jumlah</th>
                                <th>Pemakai</th>
                                <th>Verifikator</th>
                                <th>Tanggal</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                @if($tipe === 'alat')
                                    <td><strong>{{ $item->nama_alat }}</strong></td>
                                    <td>{{ $item->merek ?? '-' }}</td>
                                    <td><span class="badge bg-info">{{ $item->kategori->nama_kategori }}</span></td>
                                    <td>{{ $item->laboratorium->nama_labor }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->tipe_pelacakan == 'unit' ? 'warning' : 'success' }}">
                                            {{ ucfirst($item->tipe_pelacakan) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->jumlah_alat }}</td>
                                @elseif($tipe === 'bahan')
                                    <td><strong>{{ $item->nama_bahan }}</strong></td>
                                    <td><span class="badge bg-info">{{ $item->kategori->nama_kategori }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $item->stok_saat_ini <= $item->stok_minimum ? 'danger' : 'success' }}">
                                            {{ $item->stok_saat_ini }}
                                        </span>
                                    </td>
                                    <td>{{ $item->stok_minimum }}</td>
                                    <td>{{ $item->satuan }}</td>
                                @elseif($tipe === 'peminjaman')
                                    <td><strong>{{ $item->alat->nama_alat ?? '-' }}</strong></td>
                                    <td>{{ $item->unitAlat->kode_inventaris ?? '-' }}</td>
                                    <td>{{ $item->userPeminjam->nama ?? '-' }}</td>
                                    <td><small>{{ $item->waktu_peminjaman?->format('d-m-Y') }}</small></td>
                                    <td><small>{{ $item->waktu_pengembalian?->format('d-m-Y') }}</small></td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'terpinjam' ? 'warning' : ($item->status === 'terlambat' ? 'danger' : 'success') }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                    </td>
                                @elseif($tipe === 'pemeliharaan')
                                    <td><strong>{{ $item->unitAlat->alat->nama_alat ?? '-' }}</strong></td>
                                    <td>{{ $item->teknisi->nama ?? '-' }}</td>
                                    <td><small>{{ $item->tanggal_cek?->format('d-m-Y') ?? '-' }}</small></td>
                                    <td><span class="badge bg-info">{{ $item->kondisi ?? '-' }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $item->tanggal_cek ? 'success' : 'warning' }}">
                                            {{ $item->tanggal_cek ? 'Selesai' : 'Terjadwal' }}
                                        </span>
                                    </td>
                                @elseif($tipe === 'pengadaan_alat')
                                    <td><strong>{{ $item->alat->nama_alat ?? '-' }}</strong></td>
                                    <td>{{ $item->jumlah }}</td>
                                    <td><small>{{ $item->tanggal_pengadaan?->format('d-m-Y') ?? '-' }}</small></td>
                                    <td>
                                        <span class="badge bg-{{ $item->tanggal_masuk ? 'success' : 'warning' }}">
                                            {{ $item->tanggal_masuk ? 'Diterima' : 'Pending' }}
                                        </span>
                                    </td>
                                @elseif($tipe === 'pengadaan_bahan')
                                    <td><strong>{{ $item->bahan->nama_bahan ?? '-' }}</strong></td>
                                    <td>{{ $item->jumlah }}</td>
                                    <td><small>{{ $item->tanggal_pengadaan?->format('d-m-Y') ?? '-' }}</small></td>
                                    <td>
                                        <span class="badge bg-{{ $item->tanggal_masuk ? 'success' : 'warning' }}">
                                            {{ $item->tanggal_masuk ? 'Diterima' : 'Pending' }}
                                        </span>
                                    </td>
                                @elseif($tipe === 'pemakaian_bahan')
                                    <td><strong>{{ $item->bahan->nama_bahan ?? '-' }}</strong></td>
                                    <td>{{ $item->jumlah_terpakai ?? $item->jumlah_pengambilan }}</td>
                                    <td>{{ $item->userPemakai->nama ?? '-' }}</td>
                                    <td>{{ $item->userVerifikasi->nama ?? '-' }}</td>
                                    <td><small>{{ $item->waktu_pemakaian?->format('d-m-Y') ?? '-' }}</small></td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $tipe === 'peminjaman' || $tipe === 'pemeliharaan' ? 6 : ($tipe === 'pemakaian_bahan' ? 5 : 4) }}" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i> Tidak ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
