@extends('layouts.admin')

@section('title', 'Dashboard Teknisi')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 d-inline-block">Dashboard Teknisi</h1>
            <p class="text-muted">Jadwal Pemeliharaan & Perbaikan Alat</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Maintenance Jadwal</div>
                    <div class="h3 mb-0">{{ $maintenanceSchedule->count() }}</div>
                    <p class="text-muted small">Dalam 2 minggu ke depan</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Overdue</div>
                    <div class="h3 mb-0">{{ $overdueCount }}</div>
                    <p class="text-muted small">Maintenance yang sudah melewati jadwal</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Bulan Ini</div>
                    <div class="h3 mb-0">{{ $completedThisMonth }}</div>
                    <p class="text-muted small">Maintenance yang sudah diselesaikan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Jadwal Maintenance</h6>
                    @can('create', \App\Models\PemeliharaanAlat::class)
                    <a href="{{ route('pemeliharaan.create') }}" class="btn btn-sm btn-primary">+ Tambah Jadwal</a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($maintenanceSchedule->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode Inventaris</th>
                                        <th>Nama Alat</th>
                                        <th>Laboratorium</th>
                                        <th>Tanggal Cek</th>
                                        <th>Tanggal Cek Berikutnya</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($maintenanceSchedule as $maintenance)
                                        <tr>
                                            <td>{{ $maintenance->unitAlat->kode_inventaris }}</td>
                                            <td>{{ $maintenance->unitAlat->alat->nama_alat }}</td>
                                            <td>{{ $maintenance->unitAlat->alat->laboratorium->nama_labor }}</td>
                                            <td>{{ $maintenance->tanggal_cek?->format('d-m-Y') ?? '-' }}</td>
                                            <td>
                                                {{ $maintenance->tanggal_cek_berikutnya->format('d-m-Y') }}
                                                @if($maintenance->tanggal_cek_berikutnya < now())
                                                    <span class="badge badge-danger">OVERDUE</span>
                                                @elseif($maintenance->tanggal_cek_berikutnya <= now()->addDays(3))
                                                    <span class="badge badge-warning">URGENT</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$maintenance->tanggal_cek || $maintenance->tanggal_cek->format('Y-m-d') !== now()->format('Y-m-d'))
                                                    <span class="badge badge-warning">Belum Dikerjakan</span>
                                                @else
                                                    <span class="badge badge-success">Sudah Dikerjakan</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('pemeliharaan.show', $maintenance) }}" class="btn btn-sm btn-info">Detail</a>
                                                @can('update', $maintenance)
                                                @if(!$maintenance->tanggal_cek)
                                                    <a href="{{ route('pemeliharaan.edit', $maintenance) }}" class="btn btn-sm btn-warning">Kerjakan</a>
                                                @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Tidak ada jadwal maintenance untuk 2 minggu ke depan</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Perhatian: Maintenance Overdue</h6>
                </div>
                <div class="card-body">
                    @if($overdueCount > 0)
                        <div class="alert alert-danger" role="alert">
                            Ada <strong>{{ $overdueCount }} maintenance</strong> yang sudah melewati jadwal. Segera prioritaskan pengerjaannya!
                        </div>
                    @else
                        <div class="alert alert-success" role="alert">
                            Semua jadwal maintenance sudah terpenuhi. Bagus!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Login Sebagai:</strong> {{ Auth::user()->nama }}</p>
                            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                            <p><strong>Role:</strong> <span class="badge badge-info">{{ Auth::user()->role }}</span></p>
                        </div>
                        <div class="col-md-6 text-right">
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
    </div>
</div>
@endsection
