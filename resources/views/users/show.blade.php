@extends('layouts.admin')

@section('title', 'Detail User')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Data Users</a></li>
            <li class="breadcrumb-item active">Detail User</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $user->nama }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Email:</strong></p>
                            <p>{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Role:</strong></p>
                            <p><span class="badge bg-secondary">{{ ucfirst($user->role) }}</span></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>No. HP:</strong></p>
                            <p>{{ $user->no_hp ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>No. Induk:</strong></p>
                            <p>{{ $user->no_induk ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Status:</strong></p>
                            <p><span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'danger' }}">{{ ucfirst($user->status) }}</span></p>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($user->role == 'kalab')
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Lab Dikelola ({{ $user->laboratoriumDikelola->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @forelse($user->laboratoriumDikelola as $lab)
                            <div class="mb-2 pb-2 border-bottom">
                                <p class="mb-1"><strong>{{ $lab->nama_labor }}</strong></p>
                                <small class="text-muted">{{ $lab->lokasi }}</small>
                            </div>
                        @empty
                            <p class="text-muted text-center">Belum mengelola lab</p>
                        @endforelse
                    </div>
                </div>
            @endif

            @if($user->role == 'teknisi')
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">Pemeliharaan ({{ $user->pemeliharaanAlat->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @forelse($user->pemeliharaanAlat()->latest()->limit(5)->get() as $pemeliharaan)
                            <div class="mb-2 pb-2 border-bottom">
                                <small class="text-muted">{{ $pemeliharaan->tanggal_cek->format('d/m/Y') }}</small><br>
                                <small>{{ $pemeliharaan->unitAlat->alat->nama_alat ?? '-' }}</small>
                            </div>
                        @empty
                            <p class="text-muted text-center">Belum ada pemeliharaan</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Aktivitas Lainnya</h6>
                </div>
                <div class="card-body">
                    <p><strong>Pengadaan Alat:</strong> {{ $user->pengadaanAlat->count() }}</p>
                    <p><strong>Pengadaan Bahan:</strong> {{ $user->pengadaanBahan->count() }}</p>
                    <p><strong>Peminjaman:</strong> {{ $user->peminjamanAlat->count() }}</p>
                    <p><strong>Pemakaian Bahan:</strong> {{ $user->pemakaianBahan->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
