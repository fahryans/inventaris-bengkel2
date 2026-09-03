@extends('layouts.admin')

@section('title', 'Detail Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bahan.index') }}">Data Bahan</a></li>
            <li class="breadcrumb-item active">{{ $bahan->nama_bahan }}</li>
        </ol>
    </nav>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9] d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $bahan->nama_bahan }}</h5>
            <div>
                @can('update', $bahan)
                <a href="{{ route('bahan.edit', $bahan) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endcan
                <a href="{{ route('bahan.index') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-tabs mb-4" id="bahanTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab">
                <i class="fas fa-info-circle"></i> Detail
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="spesifikasi-tab" data-bs-toggle="tab" data-bs-target="#spesifikasi" type="button" role="tab">
                <i class="fas fa-list"></i> Spesifikasi ({{ $bahan->spesifikasiBahan->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pengadaan-tab" data-bs-toggle="tab" data-bs-target="#pengadaan" type="button" role="tab">
                <i class="fas fa-shopping-cart"></i> Pengadaan ({{ $bahan->pengadaanBahan->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pemakaian-tab" data-bs-toggle="tab" data-bs-target="#pemakaian" type="button" role="tab">
                <i class="fas fa-flask"></i> Pemakaian ({{ $bahan->pemakaianBahan->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content" id="bahanTabContent">
        {{-- TAB DETAIL --}}
        <div class="tab-pane fade show active" id="detail" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="text-muted mb-1">Kategori</p>
                                <p><span class="badge bg-info">{{ $bahan->kategori?->nama_kategori ?? '-' }}</span></p>
                            </div>
                            <div class="mb-3">
                                <p class="text-muted mb-1">Laboratorium</p>
                                <p>{{ $bahan->laboratorium?->nama_labor ?? '-' }}</p>
                            </div>
                            <div class="mb-3">
                                <p class="text-muted mb-1">Satuan</p>
                                <p>{{ $bahan->satuan }}</p>
                            </div>
                            <div class="mb-3">
                                <p class="text-muted mb-1">Stok Minimum</p>
                                <p>{{ $bahan->stok_minimum }} {{ $bahan->satuan }}</p>
                            </div>
                            <div class="mb-3">
                                <p class="text-muted mb-1">Total Stok</p>
                                <p class="fw-bold text-success">{{ $bahan->getTotalStock() }} {{ $bahan->satuan }}</p>
                            </div>
                            @if($bahan->foto)
                            <div class="mb-3">
                                <p class="text-muted mb-1">Foto</p>
                                <img src="{{ asset('storage/' . $bahan->foto) }}" alt="Foto Bahan"
                                     class="img-thumbnail" style="max-width: 200px;"
                                     onerror="this.onerror=null; this.src='{{ asset('img/no-image.svg') }}'; this.alt='Foto tidak tersedia';">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">Ringkasan per Spesifikasi</h6>
                        </div>
                        <div class="card-body">
                            @forelse($bahan->spesifikasiBahan as $spesifikasi)
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div>
                                        <strong>{{ $spesifikasi->kode_spesifikasi }}</strong><br>
                                        <small class="text-muted">{{ $spesifikasi->nama_spesifikasi }}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary">{{ $spesifikasi->getTotalStok() }} {{ $bahan->satuan }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center">Belum ada spesifikasi. Klik tab "Spesifikasi" untuk menambah.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB SPESIFIKASI --}}
        <div class="tab-pane fade" id="spesifikasi" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Daftar Spesifikasi</h6>
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addSpesifikasiModal">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Spesifikasi</th>
                                    <th>Deskripsi</th>
                                    <th>Total Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bahan->spesifikasiBahan as $spesifikasi)
                                    <tr>
                                        <td><strong>{{ $spesifikasi->kode_spesifikasi }}</strong></td>
                                        <td>{{ $spesifikasi->nama_spesifikasi }}</td>
                                        <td>{{ $spesifikasi->deskripsi ?? '-' }}</td>
                                        <td><span class="badge bg-primary">{{ $spesifikasi->getTotalStok() }} {{ $bahan->satuan }}</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editSpesifikasiModal{{ $spesifikasi->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($spesifikasi->pengadaanBahan->count() == 0)
                                            <form action="{{ route('bahan.spesifikasi.destroy', [$bahan, $spesifikasi]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus spesifikasi ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            Belum ada spesifikasi
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB PENGADAAN --}}
        <div class="tab-pane fade" id="pengadaan" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Daftar Pengadaan</h6>
                    <a href="{{ route('pengadaan_bahan.create') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-plus"></i> Tambah Pengadaan
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Spesifikasi</th>
                                    <th>Merek</th>
                                    <th>Jumlah</th>
                                    <th>Stok Tersisa</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bahan->pengadaanBahan()->latest()->get() as $pengadaan)
                                    <tr>
                                        <td>{{ $pengadaan->tanggal_pengadaan->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $pengadaan->spesifikasiBahan->kode_spesifikasi ?? '-' }}</span>
                                            <small class="text-muted">{{ $pengadaan->spesifikasiBahan->nama_spesifikasi ?? '' }}</small>
                                        </td>
                                        <td>{{ $pengadaan->merek }}</td>
                                        <td>{{ $pengadaan->jumlah }}</td>
                                        <td><span class="badge bg-success">{{ $pengadaan->stok_tersisa_batch }}</span></td>
                                        <td>{{ $pengadaan->supplier }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            Belum ada pengadaan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB PEMAKAIAN --}}
        <div class="tab-pane fade" id="pemakaian" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">Daftar Pemakaian</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Keperluan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bahan->pemakaianBahan as $pemakaian)
                                    <tr>
                                        <td>{{ $pemakaian->waktu_pemakaian->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-warning">{{ $pemakaian->jumlah_terpakai }}</span></td>
                                        <td>{{ $pemakaian->keperluan }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            Belum ada pemakaian
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH SPESIFIKASI --}}
<div class="modal fade" id="addSpesifikasiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bahan.spesifikasi.store', $bahan) }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Tambah Spesifikasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="kode_spesifikasi" class="form-label">Kode Spesifikasi <span class="text-danger">*</span></label>
                        <input type="text" name="kode_spesifikasi" id="kode_spesifikasi" class="form-control"
                               placeholder="Contoh: SPEC-01" required>
                        <small class="text-muted">Unik untuk bahan ini</small>
                    </div>
                    <div class="mb-3">
                        <label for="nama_spesifikasi" class="form-label">Nama Spesifikasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_spesifikasi" id="nama_spesifikasi" class="form-control"
                               placeholder="Contoh: Grade A Premium" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="2"
                                  placeholder="Detail tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT SPESIFIKASI --}}
@foreach($bahan->spesifikasiBahan as $spesifikasi)
<div class="modal fade" id="editSpesifikasiModal{{ $spesifikasi->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bahan.spesifikasi.update', [$bahan, $spesifikasi]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Spesifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Spesifikasi <span class="text-danger">*</span></label>
                        <input type="text" name="kode_spesifikasi" class="form-control"
                               value="{{ $spesifikasi->kode_spesifikasi }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Spesifikasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_spesifikasi" class="form-control"
                               value="{{ $spesifikasi->nama_spesifikasi }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2">{{ $spesifikasi->deskripsi }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
