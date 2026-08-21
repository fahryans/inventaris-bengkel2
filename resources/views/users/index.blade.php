@extends('layouts.admin')

@section('title', 'Data Users')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Users</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar User</h5>
            @can('create', \App\Models\User::class)
            <div class="d-flex gap-2">
                <a href="{{ route('users.bulk-create') }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-users"></i> Tambah Massal
                </a>
                <a href="{{ route('users.create') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-plus"></i> Tambah User
                </a>
            </div>
            @endcan
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari nama/email..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2">
                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Role</option>
                            <option value="admin_jurusan" {{ request('role') == 'admin_jurusan' ? 'selected' : '' }}>Admin Jurusan</option>
                            <option value="kadep" {{ request('role') == 'kadep' ? 'selected' : '' }}>Kepala Departemen</option>
                            <option value="kepala_labor" {{ request('role') == 'kepala_labor' ? 'selected' : '' }}>Kepala Laboratorium</option>
                            <option value="teknisi" {{ request('role') == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                            <option value="dosen" {{ request('role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="mahasiswa" {{ request('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-2">
                    <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>No. HP</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->nama }}</strong>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        @switch($user->role)
                                            @case('admin_jurusan') Admin Jurusan @break
                                            @case('kadep') Kepala Departemen @break
                                            @case('kepala_labor') Kepala Laboratorium @break
                                            @case('teknisi') Teknisi @break
                                            @case('dosen') Dosen @break
                                            @case('mahasiswa') Mahasiswa @break
                                            @default {{ ucfirst($user->role) }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>{{ $user->no_hp ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'danger' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $user)
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $user)
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;">
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
                                    <i class="fas fa-inbox"></i> Tidak ada data user
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
