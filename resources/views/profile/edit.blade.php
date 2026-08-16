@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Profil Saya</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            {{-- Informasi Profil --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama', $user->nama) }}" {{ Auth::user()->role === 'mahasiswa' ? 'readonly' : 'required autofocus' }}>
                            @if(Auth::user()->role === 'mahasiswa')
                                <small class="text-muted">Nama tidak dapat diubah. Hubungi admin untuk perubahan nama.</small>
                            @endif
                            @error('nama')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>

                            @if(session('status') === 'profile-updated')
                                <span class="text-success"><i class="fas fa-check-circle"></i> Berhasil disimpan.</span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Ubah Password --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">Ubah Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info Akun --}}
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Info Akun</h5>
                </div>
                <div class="card-body text-dark">
                    <p><strong>Nama:</strong> {{ $user->nama }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Role:</strong> <span class="badge bg-info">{{ $user->role }}</span></p>
                    <p><strong>Status:</strong> <span class="badge bg-success">{{ $user->status }}</span></p>
                    <p><strong>Bergabung:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            {{-- Hapus Akun --}}
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Hapus Akun</h5>
                </div>
                <div class="card-body">
                    <p class="text-dark">Setelah akun dihapus, semua data akan dihapus secara permanen.</p>
                    <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')

                        <div class="mb-3">
                            <label for="password_delete" class="form-label">Masukkan Password untuk Konfirmasi</label>
                            <input type="password" name="password" id="password_delete"
                                   class="form-control @error('password', 'delete') is-invalid @enderror" required>
                            @error('password', 'delete')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Hapus Akun
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
