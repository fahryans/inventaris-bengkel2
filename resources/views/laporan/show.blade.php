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
        <div class="card-header bg-[#5b202f] text-[#f5f0e9]">
            <h5 class="mb-0">{{ $title }}</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            @if($tipe === 'alat')
                                <th>Nama Alat</th>
                                <th>Kategori</th>
                                <th>Laboratorium</th>
                                <th>Tipe Pelacakan</th>
                                <th>Spesifikasi</th>
                                <th>Total</th>
                            @elseif($tipe === 'bahan')
                                <th>Nama Bahan</th>
                                <th>Kategori</th>
                                <th>Total Stok</th>
                                <th>Satuan</th>
                            @elseif($tipe === 'peminjaman')
                                <th>Alat</th>
                                <th>Tipe</th>
                                <th>Keperluan</th>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            @elseif($tipe === 'pemeliharaan')
                                <th>Unit Alat</th>
                                <th>Teknisi</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Status</th>
                            @elseif($tipe === 'pengadaan_alat')
                                <th>Alat</th>
                                <th>Spesifikasi</th>
                                <th>Kode Inv</th>
                                <th>Merek</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Supplier</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            @elseif($tipe === 'pengadaan_bahan')
                                <th>Bahan</th>
                                <th>Merek</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Supplier</th>
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
                                    <td><span class="badge bg-info">{{ $item->kategori?->nama_kategori ?? '-' }}</span></td>
                                    <td>{{ $item->laboratorium?->nama_labor ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->tipe_pelacakan == 'unit' ? 'warning' : 'success' }}">
                                            {{ ucfirst($item->tipe_pelacakan) }}
                                        </span>
                                    </td>
                                    <td>
                                        @foreach($item->spesifikasiAlat as $spec)
                                            <span class="badge bg-secondary">{{ $spec->kode_spesifikasi }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $item->getAvailableQuantity() }}</td>
                                @elseif($tipe === 'bahan')
                                    <td><strong>{{ $item->nama_bahan }}</strong></td>
                                    <td><span class="badge bg-info">{{ $item->kategori?->nama_kategori ?? '-' }}</span></td>
                                    <td>
                                        @php
                                            $totalStok = \App\Models\PengadaanBahan::where('id_bahan', $item->id)->sum('stok_tersisa_batch');
                                        @endphp
                                        <span class="badge bg-{{ $totalStok > 0 ? 'success' : 'danger' }}">
                                            {{ $totalStok }} {{ $item->satuan }}
                                        </span>
                                    </td>
                                    <td>{{ $item->satuan }}</td>
                                @elseif($tipe === 'peminjaman')
                                    <td><strong>{{ $item->equipment_name }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $item->equipment_type === 'Agregat' ? 'primary' : 'info' }}">
                                            {{ $item->equipment_type }}
                                        </span>
                                    </td>
                                    <td><small>{{ $item->keperluan ?? '-' }}</small></td>
                                    <td>{{ $item->userPeminjam?->nama ?? '-' }}</td>
                                    <td><small>{{ $item->waktu_peminjaman?->format('d-m-Y H:i') }}</small></td>
                                    <td>
                                        @if($item->waktu_pengembalian)
                                            @php
                                                $isReturned = $item->status === 'sudah_dikembalikan';
                                                $isOverdue = !$isReturned && $item->waktu_pengembalian < now();
                                                $returnedLate = $isReturned && $item->waktu_kembali_aktual && $item->waktu_kembali_aktual > $item->waktu_pengembalian;
                                            @endphp
                                            <small class="{{ $returnedLate ? 'text-warning fw-bold' : ($isReturned ? 'text-success' : ($isOverdue ? 'text-danger fw-bold' : 'text-muted')) }}">
                                                <i class="fas fa-{{ $returnedLate ? 'exclamation-circle' : ($isReturned ? 'check-circle' : ($isOverdue ? 'exclamation-triangle' : 'clock')) }}"></i>
                                                {{ $item->waktu_pengembalian->format('d-m-Y H:i') }}
                                                @if($returnedLate) (terlambat dikembalikan) @elseif($isOverdue) (overdue) @endif
                                            </small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
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
                                    <td><span class="badge bg-secondary">{{ $item->spesifikasiAlat->kode_spesifikasi ?? '-' }}</span></td>
                                    <td><strong>{{ $item->kode_inventaris ?? '-' }}</strong></td>
                                    <td>{{ $item->merek }}</td>
                                    <td>{{ $item->jumlah }}</td>
                                    <td>Rp {{ number_format($item->harga_perolehan, 0, ',', '.') }}</td>
                                    <td>{{ $item->supplier }}</td>
                                    <td><small>{{ $item->tanggal_pengadaan?->format('d-m-Y') ?? '-' }}</small></td>
                                    <td>
                                        <span class="badge bg-{{ $item->tanggal_masuk ? 'success' : 'warning' }}">
                                            {{ $item->tanggal_masuk ? 'Diterima' : 'Pending' }}
                                        </span>
                                    </td>
                                @elseif($tipe === 'pengadaan_bahan')
                                    <td><strong>{{ $item->bahan->nama_bahan ?? '-' }}</strong></td>
                                    <td>{{ $item->merek }}</td>
                                    <td>{{ $item->jumlah }}</td>
                                    <td>Rp {{ number_format($item->harga_perolehan, 0, ',', '.') }}</td>
                                    <td>{{ $item->supplier }}</td>
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
                                <td colspan="{{ $tipe === 'peminjaman' ? 7 : ($tipe === 'pemeliharaan' ? 6 : ($tipe === 'pemakaian_bahan' ? 5 : 4)) }}" class="text-center text-muted py-4">
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
