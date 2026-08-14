@extends('layouts.admin')

@section('title', 'Dashboard Saya')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 d-inline-block">Dashboard Saya</h1>
            <p class="text-muted">Riwayat Peminjaman Alat & Bahan</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Sedang Dipinjam</div>
                    <div class="h3 mb-0">{{ $activePeminjaman->count() }}</div>
                    <p class="text-muted small">Item yang masih harus dikembalikan</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Sudah Dikembalikan</div>
                    <div class="h3 mb-0">{{ $returnedPeminjaman->count() }}</div>
                    <p class="text-muted small">Item yang sudah dikembalikan</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small font-weight-bold">Total Transaksi</div>
                    <div class="h3 mb-0">{{ $myPeminjaman->count() }}</div>
                    <p class="text-muted small">Semua peminjaman</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Peminjaman Aktif</h6>
                    @can('create', \App\Models\PeminjamanAlat::class)
                    <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-primary">+ Pinjam Alat/Bahan</a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($activePeminjaman->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Alat/Bahan</th>
                                        <th>Keperluan</th>
                                        <th>Dipinjam Sejak</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activePeminjaman as $peminjaman)
                                        <tr>
                                            <td>{{ $peminjaman->equipment_name }}</td>
                                            <td>{{ $peminjaman->keperluan }}</td>
                                            <td>{{ $peminjaman->waktu_peminjaman->format('d-m-Y H:i') }}</td>
                                            <td>
                                                {{ $peminjaman->waktu_pengembalian?->format('d-m-Y H:i') }}
                                                @if($peminjaman->isOverdue())
                                                    <span class="badge badge-danger">{{ $peminjaman->getDaysOverdue() }} hari overdue</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">Terpinjam</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-sm btn-info">Lihat</a>
                                                @can('return', $peminjaman)
                                                <a href="{{ route('peminjaman.show', $peminjaman) }}#return-form" class="btn btn-sm btn-success">Kembalikan</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            Anda tidak sedang meminjam apapun.
                            @can('create', \App\Models\PeminjamanAlat::class)
                            <a href="{{ route('peminjaman.create') }}">Pinjam sekarang →</a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Riwayat Peminjaman (10 Terakhir)</h6>
                </div>
                <div class="card-body">
                    @if($myPeminjaman->count())
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Alat/Bahan</th>
                                        <th>Keperluan</th>
                                        <th>Tanggal Peminjaman</th>
                                        <th>Status</th>
                                        <th>Kondisi Awal</th>
                                        <th>Kondisi Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myPeminjaman as $peminjaman)
                                        <tr>
                                            <td>{{ $peminjaman->equipment_name }}</td>
                                            <td>{{ $peminjaman->keperluan }}</td>
                                            <td>{{ $peminjaman->waktu_peminjaman->format('d-m-Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $peminjaman->status === 'terpinjam' ? 'warning' : 'success' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $peminjaman->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $peminjaman->kondisi_saat_peminjaman ?? '-' }}</td>
                                            <td>{{ $peminjaman->kondisi_saat_pengembalian ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Belum ada riwayat peminjaman</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Petunjuk Penggunaan</h6>
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li>Klik tombol <strong>"+ Pinjam Alat/Bahan"</strong> untuk membuat permintaan peminjaman</li>
                        <li>Pilih alat atau bahan yang ingin dipinjam</li>
                        <li>Isi keperluan dan durasi peminjaman</li>
                        <li>Tunggu persetujuan dari kepala laboratorium</li>
                        <li>Setelah disetujui, ambil alat/bahan ke lokasi yang ditentukan</li>
                        <li>Kembalikan sesuai jadwal yang telah ditetapkan</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Informasi Penting</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <strong>⚠️ Perhatian:</strong> Harap kembalikan alat/bahan tepat waktu sesuai jadwal yang telah ditentukan.
                    </div>
                    <div class="alert alert-info" role="alert">
                        <strong>ℹ️ Info:</strong> Periksa kondisi alat/bahan saat menerima dan melaporkan kerusakan segera.
                    </div>
                    <hr>
                    <p><strong>Login Sebagai:</strong> {{ Auth::user()->nama }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <p><strong>Role:</strong> <span class="badge badge-info">{{ Auth::user()->role }}</span></p>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                    <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
