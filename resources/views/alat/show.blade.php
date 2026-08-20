@extends('layouts.admin')

@section('title', 'Detail Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('alat.index') }}">Data Alat</a></li>
            <li class="breadcrumb-item active">{{ $alat->nama_alat }}</li>
        </ol>
    </nav>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9] d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $alat->nama_alat }}</h5>
            <div>
                @can('update', $alat)
                <a href="{{ route('alat.edit', $alat) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endcan
                <a href="{{ route('alat.index') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-tabs mb-4" id="alatTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab">
                <i class="fas fa-info-circle"></i> Detail
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="spesifikasi-tab" data-bs-toggle="tab" data-bs-target="#spesifikasi" type="button" role="tab">
                <i class="fas fa-list"></i> Spesifikasi ({{ $alat->spesifikasiAlat->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pengadaan-tab" data-bs-toggle="tab" data-bs-target="#pengadaan" type="button" role="tab">
                <i class="fas fa-shopping-cart"></i> Pengadaan ({{ $alat->pengadaanAlat->count() }})
            </button>
        </li>
        @if($alat->isUnitTracked())
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="unit-tab" data-bs-toggle="tab" data-bs-target="#unit" type="button" role="tab">
                <i class="fas fa-cube"></i> Unit ({{ $alat->unitAlat->count() }})
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content" id="alatTabContent">
        {{-- TAB DETAIL --}}
        <div class="tab-pane fade show active" id="detail" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="text-muted mb-1">Kategori</p>
                                <p><span class="badge bg-info">{{ $alat->kategori?->nama_kategori ?? '-' }}</span></p>
                            </div>
                            <div class="mb-3">
                                <p class="text-muted mb-1">Laboratorium</p>
                                <p>{{ $alat->laboratorium?->nama_labor ?? '-' }}</p>
                            </div>
                            <div class="mb-3">
                                <p class="text-muted mb-1">Tipe Pelacakan</p>
                                <p><span class="badge bg-{{ $alat->tipe_pelacakan == 'unit' ? 'warning' : 'success' }}">{{ ucfirst($alat->tipe_pelacakan) }}</span></p>
                            </div>
                            <div class="mb-3">
                                <p class="text-muted mb-1">Total Jumlah</p>
                                <p class="fw-bold">{{ $alat->getAvailableQuantity() }} unit</p>
                            </div>
                            @if($alat->foto)
                            <div class="mb-3">
                                <p class="text-muted mb-1">Foto</p>
                                <img src="{{ asset('storage/' . $alat->foto) }}" alt="Foto Alat" class="img-thumbnail" style="max-width: 200px;">
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
                            @forelse($alat->spesifikasiAlat as $spesifikasi)
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div>
                                        <strong>{{ $spesifikasi->kode_spesifikasi }}</strong><br>
                                        <small class="text-muted">{{ $spesifikasi->nama_spesifikasi }}</small>
                                    </div>
                                    <div>
                                        @if($alat->isUnitTracked())
                                            <span class="badge bg-primary">{{ $spesifikasi->unitAlat->count() }} unit</span>
                                        @else
                                            <span class="badge bg-primary">{{ $spesifikasi->getTotalUnit() }} pcs</span>
                                        @endif
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

        {{-- TAB SPEKSIFIKASI --}}
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
                                    <th>Total</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alat->spesifikasiAlat as $spesifikasi)
                                    <tr>
                                        <td><strong>{{ $spesifikasi->kode_spesifikasi }}</strong></td>
                                        <td>{{ $spesifikasi->nama_spesifikasi }}</td>
                                        <td>{{ $spesifikasi->deskripsi ?? '-' }}</td>
                                        <td>
                                            @if($alat->isUnitTracked())
                                                {{ $spesifikasi->unitAlat->count() }} unit
                                            @else
                                                {{ $spesifikasi->getTotalUnit() }} pcs
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" 
                                                    data-bs-target="#editSpesifikasiModal{{ $spesifikasi->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($spesifikasi->pengadaanAlat->count() == 0 && $spesifikasi->unitAlat->count() == 0)
                                            <form action="{{ route('alat.spesifikasi.destroy', [$alat, $spesifikasi]) }}" method="POST" style="display:inline;">
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
                    <a href="{{ route('pengadaan_alat.create') }}" class="btn btn-sm btn-light">
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
                                    <th>Kode Inv</th>
                                    <th>Merek</th>
                                    <th>Jumlah</th>
                                    <th>Harga</th>
                                    <th>Supplier</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alat->pengadaanAlat()->latest()->get() as $pengadaan)
                                    <tr>
                                        <td>{{ $pengadaan->tanggal_pengadaan->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-secondary">{{ $pengadaan->spesifikasiAlat->kode_spesifikasi ?? '-' }}</span></td>
                                        <td><strong>{{ $pengadaan->kode_inventaris }}</strong></td>
                                        <td>{{ $pengadaan->merek }}</td>
                                        <td>{{ $pengadaan->jumlah }}</td>
                                        <td>Rp {{ number_format($pengadaan->harga_perolehan, 0, ',', '.') }}</td>
                                        <td>{{ $pengadaan->supplier }}</td>
                                        <td>
                                            @if($pengadaan->tanggal_masuk)
                                                <span class="badge bg-success">Diterima</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">
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

        {{-- TAB UNIT --}}
        @if($alat->isUnitTracked())
        <div class="tab-pane fade" id="unit" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Daftar Unit Alat</h6>
                    <a href="{{ route('unit-alat.create') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-plus"></i> Tambah Unit
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Inventaris</th>
                                    <th>Spesifikasi</th>
                                    <th>Kondisi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alat->unitAlat as $unit)
                                    <tr>
                                        <td><strong>{{ $unit->kode_inventaris }}</strong></td>
                                        <td><span class="badge bg-secondary">{{ $unit->spesifikasiAlat->kode_spesifikasi ?? '-' }}</span></td>
                                        <td>
                                            <span class="badge bg-{{ $unit->kondisi_saat_ini == 'baik' ? 'success' : ($unit->kondisi_saat_ini == 'rusak_ringan' ? 'warning' : 'danger') }}">
                                                {{ ucfirst(str_replace('_', ' ', $unit->kondisi_saat_ini)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $unit->status == 'tersedia' ? 'success' : 'danger' }}">
                                                {{ ucfirst($unit->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('unit-alat.show', $unit) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            Belum ada unit
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH SPEKSIFIKASI --}}
<div class="modal fade" id="addSpesifikasiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('alat.spesifikasi.store', $alat) }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Tambah Spesifikasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_spesifikasi" class="form-label">Kode Spesifikasi <span class="text-danger">*</span></label>
                        <input type="text" name="kode_spesifikasi" id="kode_spesifikasi" class="form-control" 
                               placeholder="Contoh: PS-12V-1A" required>
                        <small class="text-muted">Unik untuk alat ini</small>
                    </div>
                    <div class="mb-3">
                        <label for="nama_spesifikasi" class="form-label">Nama Spesifikasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_spesifikasi" id="nama_spesifikasi" class="form-control" 
                               placeholder="Contoh: 12V 1 Ampere" required>
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

{{-- MODAL EDIT SPEKSIFIKASI --}}
@foreach($alat->spesifikasiAlat as $spesifikasi)
<div class="modal fade" id="editSpesifikasiModal{{ $spesifikasi->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('alat.spesifikasi.update', [$alat, $spesifikasi]) }}" method="POST">
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
