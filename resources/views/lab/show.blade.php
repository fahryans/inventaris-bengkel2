@extends('layouts.admin')

@section('title', $lab->nama_labor)

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">{{ $lab->nama_labor }}</li>
        </ol>
    </nav>

    {{-- Info Lab --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-4" style="width: 70px; height: 70px;">
                    <i class="fas fa-building fa-2x"></i>
                </div>
                <div>
                    <h3 class="mb-1">{{ $lab->nama_labor }}</h3>
                    <p class="text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i> {{ $lab->lokasi }}</p>
                    <p class="text-muted mb-1"><i class="fas fa-user-tie me-1"></i> Ka. Lab: {{ $lab->kalab->nama ?? '-' }}</p>
                    @if($lab->teknisi->count())
                        <p class="text-muted mb-0">
                            <i class="fas fa-wrench me-1"></i> Teknisi: 
                            @foreach($lab->teknisi as $t)
                                <span class="badge bg-info">{{ $t->nama }}</span>
                            @endforeach
                        </p>
                    @endif
                    @if($lab->sop)
                        <div class="mt-3">
                            <button type="button" class="btn p-0 border-0 bg-transparent text-start d-block w-100"
                                    data-bs-toggle="modal" data-bs-target="#viewSopModal" title="Lihat SOP">
                                <div class="sop-card p-3 d-flex align-items-center">
                                    <div class="sop-icon me-3">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <span class="text-uppercase fw-bold sop-label me-2">SOP</span>
                                            <span class="badge sop-badge">Lihat SOP</span>
                                        </div>
                                        <div class="sop-preview text-muted mt-1">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($lab->sop), 60) }}
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right sop-arrow"></i>
                                </div>
                            </button>
                            @can('update', $lab)
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-soft-primary" data-bs-toggle="modal" data-bs-target="#editSopModal" title="Edit SOP">
                                    <i class="fas fa-edit me-1"></i> Edit SOP
                                </button>
                                <a href="#" class="btn btn-sm btn-soft-secondary ms-1" data-bs-toggle="modal" data-bs-target="#viewSopModal">
                                    <i class="fas fa-eye me-1"></i> Lihat
                                </a>
                            </div>
                            @endcan
                        </div>
                    @else
                        <div class="mt-3">
                            <div class="sop-card-empty p-3 d-flex align-items-center">
                                <div class="sop-icon me-3">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="text-uppercase fw-bold sop-label">SOP</span>
                                    <div class="text-muted small mt-1">Belum tersedia</div>
                                </div>
                            </div>
                            @can('update', $lab)
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-soft-primary" data-bs-toggle="modal" data-bs-target="#editSopModal" title="Tambah SOP">
                                    <i class="fas fa-plus me-1"></i> Tambah SOP
                                </button>
                            </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Alat --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-screwdriver-wrench me-1"></i> Daftar Alat ({{ $alat->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($alat->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:40px;" class="text-center"><input type="checkbox" class="form-check-input check-all-alat" title="Pilih semua"></th>
                                        <th>Nama Alat</th>
                                        <th>Tipe</th>
                                        <th>Stok/Unit</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alat as $item)
                                        <tr>
                                            <td class="text-center">
                                                @if($item->tipe_pelacakan === 'agregat' && max(0, ($item->pengadaan_alat_sum_jumlah ?? 0) - ($item->peminjaman_alat_sum_jumlah ?? 0)) > 0)
                                                    <input type="checkbox" class="form-check-input cb-alat" data-type="alat" data-id="{{ $item->id }}" data-name="{{ $item->nama_alat }}" data-tipe="agregat" data-satuan="unit">
                                                @elseif($item->tipe_pelacakan === 'unit' && $item->unitAlat->where('status', 'tersedia')->count() > 0)
                                                    @php $availUnit = $item->unitAlat->firstWhere('status', 'tersedia'); @endphp
                                                    <input type="checkbox" class="form-check-input cb-alat" data-type="alat" data-id="{{ $availUnit->id }}" data-name="{{ $item->nama_alat }} ({{ $availUnit->kode_inventaris }})" data-tipe="unit" data-satuan="unit">
                                                @else
                                                    <input type="checkbox" class="form-check-input cb-alat" disabled title="Stok habis">
                                                @endif
                                            </td>
                                            <td>{{ $item->nama_alat }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->tipe_pelacakan === 'agregat' ? 'primary' : 'info' }}">
                                                    {{ ucfirst($item->tipe_pelacakan) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->tipe_pelacakan === 'agregat')
                                                    {{ max(0, ($item->pengadaan_alat_sum_jumlah ?? 0) - ($item->peminjaman_alat_sum_jumlah ?? 0)) }} unit
                                                @else
                                                    {{ $item->unitAlat->where('status', 'tersedia')->count() }} / {{ $item->unitAlat->count() }} tersedia
                                                @endif
                                            </td>
                                            <td>
                                                @can('create', \App\Models\PeminjamanAlat::class)
                                                    @if($item->tipe_pelacakan === 'agregat' && max(0, ($item->pengadaan_alat_sum_jumlah ?? 0) - ($item->peminjaman_alat_sum_jumlah ?? 0)) > 0)
                                                        <button type="button" class="btn btn-sm btn-primary btn-pinjam"
                                                                data-tipe="agregat" data-id="{{ $item->id }}" data-name="{{ $item->nama_alat }}"
                                                                data-satuan="unit"
                                                                data-spesifikasi='{!! json_encode($item->spesifikasiAlat->map(fn($s) => ["id" => $s->id, "kode" => $s->kode_spesifikasi, "nama" => $s->nama_spesifikasi, "stok" => $s->stok_tersedia ?? 0, "satuan" => $s->satuan_label ?? "unit"])) !!}'>
                                                            <i class="fas fa-handshake"></i> Pinjam
                                                        </button>
                                                    @elseif($item->tipe_pelacakan === 'unit' && $item->unitAlat->where('status', 'tersedia')->count() > 0)
                                                        @php $availableUnit = $item->unitAlat->firstWhere('status', 'tersedia'); @endphp
                                                        <button type="button" class="btn btn-sm btn-primary btn-pinjam"
                                                                data-tipe="unit" data-id="{{ $availableUnit->id }}" data-name="{{ $item->nama_alat }} ({{ $availableUnit->kode_inventaris }})"
                                                                data-satuan="unit"
                                                                data-spesifikasi='{!! json_encode($item->spesifikasiAlat->map(fn($s) => ["id" => $s->id, "kode" => $s->kode_spesifikasi, "nama" => $s->nama_spesifikasi, "stok" => $s->stok_tersedia ?? 0, "satuan" => $s->satuan_label ?? "unit"])) !!}'>
                                                            <i class="fas fa-handshake"></i> Pinjam
                                                        </button>
                                                    @else
                                                        <span class="badge bg-secondary">Stok Habis</span>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $alat->links() }}
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            Belum ada alat di laboratorium ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Bahan --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-flask me-1"></i> Daftar Bahan ({{ $bahan->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($bahan->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:40px;" class="text-center"><input type="checkbox" class="form-check-input check-all-bahan" title="Pilih semua"></th>
                                        <th>Nama Bahan</th>
                                        <th>Stok</th>
                                        <th>Satuan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bahan as $item)
                                        <tr>
                                            <td class="text-center">
                                                @if(($item->pengadaan_bahan_sum_stok_tersisa_batch ?? 0) > 0)
                                                    <input type="checkbox" class="form-check-input cb-bahan" data-type="bahan" data-id="{{ $item->id }}" data-name="{{ $item->nama_bahan }}" data-satuan="{{ $item->satuan }}"
                                                           data-spesifikasi='{!! json_encode($item->spesifikasiBahan->map(fn($s) => ["id" => $s->id, "kode" => $s->kode_spesifikasi, "nama" => $s->nama_spesifikasi, "stok" => $s->stok_tersedia ?? 0])) !!}'>
                                                @else
                                                    <input type="checkbox" class="form-check-input cb-bahan" disabled title="Stok habis">
                                                @endif
                                            </td>
                                            <td>{{ $item->nama_bahan }}</td>
                                            <td>{{ $item->pengadaan_bahan_sum_stok_tersisa_batch ?? 0 }}</td>
                                            <td>{{ $item->satuan }}</td>
                                            <td>
                                                @if(($item->pengadaan_bahan_sum_stok_tersisa_batch ?? 0) < $item->stok_minimum)
                                                    <span class="badge bg-danger">Stok Menipis</span>
                                                @else
                                                    <span class="badge bg-success">Aman</span>
                                                @endif
                                            </td>
                                            <td>
                                                @can('create', \App\Models\PemakaianBahan::class)
                                                    @if(($item->pengadaan_bahan_sum_stok_tersisa_batch ?? 0) > 0)
                                                        <button type="button" class="btn btn-sm btn-success btn-pakai-bahan"
                                                                data-id="{{ $item->id }}"
                                                                data-name="{{ $item->nama_bahan }}"
                                                                data-stok="{{ $item->pengadaan_bahan_sum_stok_tersisa_batch ?? 0 }}"
                                                                data-satuan="{{ $item->satuan }}"
                                                                data-spesifikasi='{!! json_encode($item->spesifikasiBahan->map(fn($s) => ["id" => $s->id, "kode" => $s->kode_spesifikasi, "nama" => $s->nama_spesifikasi, "stok" => $s->stok_tersedia ?? 0, "satuan" => $item->satuan])) !!}'>
                                                            <i class="fas fa-flask"></i> Pakai
                                                        </button>
                                                    @else
                                                        <span class="badge bg-secondary">Stok Habis</span>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $bahan->links() }}
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            Belum ada bahan di laboratorium ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Quick Pinjam -->
<div class="modal fade" id="modalPinjam" tabindex="-1" aria-labelledby="modalPinjamLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formQuickPinjam" method="POST" action="{{ route('peminjaman.quick') }}">
                @csrf
                <input type="hidden" name="tipe" id="modalTipe">
                <input type="hidden" name="id_alat" id="modalIdAlat">
                <input type="hidden" name="id_unit_alat" id="modalIdUnit">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalPinjamLabel">
                        <i class="fas fa-handshake me-2"></i>Pinjam Alat
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alat</label>
                        <p class="form-control-plaintext" id="modalAlatName">-</p>
                    </div>
                    <div class="mb-3" id="modalSpesifikasiWrapper">
                        <label for="modalIdSpesifikasi" class="form-label">Spesifikasi Alat <span class="text-danger">*</span></label>
                        <select name="id_spesifikasi_alat" id="modalIdSpesifikasi" class="form-select" required>
                            <option value="">Pilih Spesifikasi</option>
                        </select>
                        <div id="modalSpesifikasiInfo" class="alert alert-info d-none mt-2 mb-0 py-1 px-2" style="font-size: 0.85rem;"></div>
                        <small class="text-muted">Pilih spesifikasi alat yang akan dipinjam</small>
                    </div>
                    <div class="mb-3">
                        <label for="modalKeperluan" class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="keperluan" id="modalKeperluan" class="form-control" required
                               placeholder="Contoh: Praktikum Jaringan" maxlength="255">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i> Pinjam Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pakai Bahan -->
<div class="modal fade" id="pakaiBahanModal" tabindex="-1" aria-labelledby="pakaiBahanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="pakaiBahanForm" action="{{ route('pemakaian_bahan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_bahan" id="lab_hidden_id_bahan">
                <input type="hidden" name="waktu_pemakaian" id="lab_waktu_pemakaian">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="pakaiBahanModalLabel">
                        <i class="fas fa-flask me-2"></i>Pakai Bahan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Bahan</label>
                        <p class="form-control-plaintext" id="pakaiBahanName">-</p>
                    </div>
                    <div class="mb-3" id="pakaiSpesifikasiWrapper">
                        <label for="lab_id_spesifikasi_bahan" class="form-label">Spesifikasi Bahan <span class="text-danger">*</span></label>
                        <select name="id_spesifikasi_bahan" id="lab_id_spesifikasi_bahan" class="form-select" required>
                            <option value="">Pilih Spesifikasi</option>
                        </select>
                        <div id="pakaiSpesifikasiInfo" class="alert alert-info d-none mt-2 mb-0 py-1 px-2" style="font-size: 0.85rem;"></div>
                        <small class="text-muted">Pilih spesifikasi bahan yang akan dipakai</small>
                    </div>
                    <div class="mb-3">
                        <label for="lab_jumlah_pengambilan" class="form-label">Jumlah Diambil <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_pengambilan" id="lab_jumlah_pengambilan" class="form-control"
                               min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="lab_keperluan_pakai" class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="keperluan" id="lab_keperluan_pakai" class="form-control"
                               placeholder="Contoh: Praktikum TIG Welding" required>
                    </div>
                    <div id="lab_pakaiBahanError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="lab_btnPakaiBahanSubmit">
                        <i class="fas fa-check"></i> Pakai Bahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Lihat SOP -->
<div class="modal fade" id="viewSopModal" tabindex="-1" aria-labelledby="viewSopModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header sop-modal-header">
                <div class="d-flex align-items-center">
                    <div class="sop-icon me-3"><i class="fas fa-book-open"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="viewSopModalLabel">Standard Operating Procedure</h5>
                        <small class="text-white-50">{{ $lab->nama_labor }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($lab->sop)
                    <div class="sop-content">{!! $lab->sop !!}</div>
                @else
                    <p class="text-muted text-center py-4">
                        <i class="fas fa-book-open fa-2x mb-2 d-block opacity-50"></i>
                        SOP belum tersedia untuk laboratorium ini.
                    </p>
                @endif
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit SOP -->
<div class="modal fade" id="editSopModal" tabindex="-1" aria-labelledby="editSopModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('lab.sop.update', $lab) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header sop-modal-header">
                    <div class="d-flex align-items-center">
                        <div class="sop-icon me-3"><i class="fas fa-edit"></i></div>
                        <div>
                            <h5 class="modal-title mb-0" id="editSopModalLabel">Edit SOP</h5>
                            <small class="text-white-50">{{ $lab->nama_labor }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Quill editor container -->
                    <div id="sopEditor" style="height: 360px;"></div>
                    <!-- Hidden textarea untuk menyimpan HTML (nama 'sop') -->
                    <textarea name="sop" id="sopEditorHidden" class="d-none"></textarea>
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-info-circle me-1"></i>
                        Gunakan toolbar seperti MS Word. Poin & sub-poin dibuat dengan list (bullet/number), bisa bersarang.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan SOP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- PANEL KERANJANG MASSAL --}}
<form id="massBorrowForm" action="{{ route('lab.borrow_mass', $lab) }}" method="POST">
    @csrf
    <div id="massBorrowPanel" class="mass-borrow-panel d-none">
        <div class="mass-borrow-header">
            <div>
                <i class="fas fa-shopping-cart me-2"></i> Pinjam Massal
                <span class="badge mass-borrow-count" id="massBorrowCount">0</span>
            </div>
            <button type="button" class="btn-close btn-close-white" id="massBorrowClose"></button>
        </div>
        <div class="mass-borrow-body" id="massBorrowItems"></div>
        <div class="mass-borrow-footer">
            <button type="button" class="btn btn-light btn-sm" id="massBorrowClear">Kosongkan</button>
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-check me-1"></i> Simpan Massal
            </button>
        </div>
        <div id="massBorrowError" class="mass-borrow-error d-none"></div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === PINJAM ALAT ===
    const modal = new bootstrap.Modal(document.getElementById('modalPinjam'));
    const form = document.getElementById('formQuickPinjam');
    const inputTipe = document.getElementById('modalTipe');
    const inputIdAlat = document.getElementById('modalIdAlat');
    const inputIdUnit = document.getElementById('modalIdUnit');
    const inputIdSpesifikasi = document.getElementById('modalIdSpesifikasi');
    const inputName = document.getElementById('modalAlatName');
    const inputKeperluan = document.getElementById('modalKeperluan');
    const spesifikasiWrapper = document.getElementById('modalSpesifikasiWrapper');
    const spesifikasiInfo = document.getElementById('modalSpesifikasiInfo');
    const satuanTextSpan = document.getElementById('modalSatuanText');

    document.querySelectorAll('.btn-pinjam').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tipe = this.dataset.tipe;
            const id = this.dataset.id;
            const name = this.dataset.name;
            const satuan = this.dataset.satuan || 'unit';
            const spesifikasiData = this.dataset.spesifikasi;

            inputTipe.value = tipe;
            inputName.textContent = name;
            inputKeperluan.value = '';
            inputIdSpesifikasi.value = '';
            if (spesifikasiInfo) spesifikasiInfo.classList.add('d-none');

            if (tipe === 'agregat') {
                inputIdAlat.value = id;
                inputIdUnit.value = '';
            } else {
                inputIdAlat.value = '';
                inputIdUnit.value = id;
            }

            // Populate spesifikasi dropdown
            inputIdSpesifikasi.innerHTML = '<option value="">Pilih Spesifikasi</option>';
            if (spesifikasiData) {
                const spesifikasis = JSON.parse(spesifikasiData.replace(/&quot;/g, '"'));
                if (spesifikasis.length > 0) {
                    spesifikasiWrapper.classList.remove('d-none');
                    spesifikasis.forEach(function(spec) {
                        const option = document.createElement('option');
                        option.value = spec.id;
                        option.dataset.stok = spec.stok;
                        option.dataset.satuan = spec.satuan || satuan;
                        option.textContent = spec.kode + ' - ' + spec.nama + ' (Stok: ' + spec.stok + ' ' + (spec.satuan || satuan) + ')';
                        inputIdSpesifikasi.appendChild(option);
                    });
                } else {
                    spesifikasiWrapper.classList.add('d-none');
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Tidak ada spesifikasi';
                    inputIdSpesifikasi.appendChild(option);
                }
            }

            modal.show();
            setTimeout(() => {
                if (inputIdSpesifikasi.options.length > 1) inputIdSpesifikasi.focus();
                else inputKeperluan.focus();
            }, 300);
        });
    });

    // Tampilkan info stok saat spesifikasi alat dipilih
    inputIdSpesifikasi.addEventListener('change', function() {
        if (spesifikasiInfo && this.selectedIndex > 0) {
            const opt = this.options[this.selectedIndex];
            spesifikasiInfo.innerHTML = '<i class="fas fa-box me-1"></i> Jumlah tersedia: <strong>' + opt.dataset.stok + ' ' + (opt.dataset.satuan || 'unit') + '</strong>';
            spesifikasiInfo.classList.remove('d-none');
        } else if (spesifikasiInfo) {
            spesifikasiInfo.classList.add('d-none');
        }
    });

    // === PAKAI BAHAN ===
    var bahanModal = new bootstrap.Modal(document.getElementById('pakaiBahanModal'));
    var bahanForm = document.getElementById('pakaiBahanForm');
    var bahanName = document.getElementById('pakaiBahanName');
    var pakaiSpesifikasiSelect = document.getElementById('lab_id_spesifikasi_bahan');
    var pakaiSpesifikasiWrapper = document.getElementById('pakaiSpesifikasiWrapper');
    var pakaiSpesifikasiInfo = document.getElementById('pakaiSpesifikasiInfo');

    document.querySelectorAll('.btn-pakai-bahan').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var name = this.dataset.name;
            var stok = this.dataset.stok;
            var satuan = this.dataset.satuan;
            var spesifikasiData = this.dataset.spesifikasi;

            document.getElementById('lab_hidden_id_bahan').value = id;
            document.getElementById('lab_waktu_pemakaian').value = new Date().toISOString().slice(0, 16);
            bahanName.textContent = name + ' (Stok: ' + stok + ' ' + satuan + ')';

            document.getElementById('lab_jumlah_pengambilan').value = '';
            document.getElementById('lab_keperluan_pakai').value = '';
            document.getElementById('lab_pakaiBahanError').classList.add('d-none');
            if (pakaiSpesifikasiInfo) pakaiSpesifikasiInfo.classList.add('d-none');

            // Populate spesifikasi dropdown
            pakaiSpesifikasiSelect.innerHTML = '<option value="">Pilih Spesifikasi</option>';
            if (spesifikasiData) {
                const spesifikasis = JSON.parse(spesifikasiData.replace(/&quot;/g, '"'));
                if (spesifikasis.length > 0) {
                    pakaiSpesifikasiWrapper.classList.remove('d-none');
                    spesifikasis.forEach(function(spec) {
                        const option = document.createElement('option');
                        option.value = spec.id;
                        option.dataset.stok = spec.stok;
                        option.dataset.satuan = spec.satuan || satuan;
                        option.textContent = spec.kode + ' - ' + spec.nama + ' (Stok: ' + spec.stok + ' ' + (spec.satuan || satuan) + ')';
                        pakaiSpesifikasiSelect.appendChild(option);
                    });
                } else {
                    pakaiSpesifikasiWrapper.classList.add('d-none');
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Tidak ada spesifikasi';
                    pakaiSpesifikasiSelect.appendChild(option);
                }
            }

            bahanModal.show();
        });
    });

    // Tampilkan info stok saat spesifikasi bahan dipilih
    pakaiSpesifikasiSelect.addEventListener('change', function() {
        if (pakaiSpesifikasiInfo && this.selectedIndex > 0) {
            const opt = this.options[this.selectedIndex];
            pakaiSpesifikasiInfo.innerHTML = '<i class="fas fa-box me-1"></i> Jumlah tersedia: <strong>' + opt.dataset.stok + ' ' + (opt.dataset.satuan || '') + '</strong>';
            pakaiSpesifikasiInfo.classList.remove('d-none');
        } else if (pakaiSpesifikasiInfo) {
            pakaiSpesifikasiInfo.classList.add('d-none');
        }
    });

    bahanForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('lab_btnPakaiBahanSubmit');
        var errorDiv = document.getElementById('lab_pakaiBahanError');

        document.getElementById('lab_waktu_pemakaian').value = new Date().toISOString().slice(0, 16);

        // Validasi: spesifikasi harus dipilih
        if (!pakaiSpesifikasiSelect.value) {
            errorDiv.textContent = 'Pilih spesifikasi bahan terlebih dahulu';
            errorDiv.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        errorDiv.classList.add('d-none');

        fetch(bahanForm.action, {
            method: 'POST',
            body: new FormData(bahanForm),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) {
            if (response.ok) {
                window.location.reload();
            } else {
                return response.json().then(function(data) {
                    throw new Error(data.message || Object.values(data.errors || {}).flat().join(', '));
                });
            }
        })
        .catch(function(err) {
            errorDiv.textContent = err.message;
            errorDiv.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Pakai Bahan';
        });
    });
});
</script>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
/* ==== Kartu SOP (di header lab) ==== */
.sop-card {
    background: linear-gradient(135deg, #1e88e5 0%, #42a5f5 100%);
    border-radius: 1rem;
    color: #fff;
    box-shadow: 0 6px 18px rgba(30, 136, 229, .30);
    transition: transform .2s ease, box-shadow .2s ease;
    max-width: 560px;
}
.sop-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(30, 136, 229, .42);
}
.sop-card-empty {
    background: #f0f7ff;
    border: 1px dashed #9dc9f0;
    border-radius: 1rem;
    color: #6c757d;
    max-width: 560px;
}
.sop-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: rgba(255,255,255,.20);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.sop-card-empty .sop-icon {
    background: #d9ecfc;
    color: #2f7fd4;
}
.sop-label {
    font-size: .8rem;
    letter-spacing: .14em;
    color: #e3f2fd;
}
.sop-badge {
    background: rgba(255,255,255,.22);
    color: #fff;
    font-size: .68rem;
    padding: .28em .65em;
    border-radius: 999px;
}
.sop-preview {
    font-size: .82rem;
    color: #eaf4fd;
    line-height: 1.4;
}
.sop-arrow {
    color: rgba(255,255,255,.60);
    font-size: .9rem;
    flex-shrink: 0;
}

/* Tombol soft */
.btn-soft-primary {
    background: #e3f2fd;
    color: #1e6bc4;
    border: 1px solid #bcdff7;
}
.btn-soft-primary:hover {
    background: #d0e9fb;
    color: #16549c;
}
.btn-soft-secondary {
    background: #f1f1f1;
    color: #555;
    border: 1px solid #ddd;
}
.btn-soft-secondary:hover {
    background: #e6e6e6;
    color: #333;
}

/* ==== Konten SOP di modal lihat ==== */
.sop-content {
    line-height: 1.75;
    color: #2c2c2c;
    font-size: .95rem;
}
.sop-content h1, .sop-content h2, .sop-content h3, .sop-content h4,
.sop-content h5, .sop-content h6 {
    color: #1e88e5;
    margin-top: 1rem;
    margin-bottom: .5rem;
    font-weight: 600;
}
.sop-content ul, .sop-content ol { padding-left: 1.6rem; }
.sop-content li { margin-bottom: .3rem; }
.sop-content blockquote {
    border-left: 4px solid #42a5f5;
    background: #f0f7ff;
    padding: .6rem 1rem;
    border-radius: 0 .5rem .5rem 0;
    color: #555;
    margin: .75rem 0;
}
.sop-content a { color: #0d6efd; text-decoration: underline; }
.sop-content pre, .sop-content code {
    background: #f4f4f4;
    border-radius: .35rem;
    padding: .15rem .4rem;
    font-size: .85em;
}
.sop-content table { width: 100%; border-collapse: collapse; margin: .5rem 0; }
.sop-content th, .sop-content td { border: 1px solid #dee2e6; padding: .4rem .6rem; }
.sop-content th { background: #f5f5f5; }

/* ==== Modal SOP ==== */
.sop-modal-header {
    background: linear-gradient(135deg, #1e88e5 0%, #42a5f5 100%);
    color: #fff;
}
.sop-modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

/* ==== Panel Keranjang Massal ==== */
.mass-borrow-panel {
    position: fixed;
    right: 20px;
    bottom: 20px;
    width: 360px;
    max-width: calc(100vw - 40px);
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 12px 40px rgba(0,0,0,.2);
    z-index: 1050;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 70vh;
}
.mass-borrow-header {
    background: linear-gradient(135deg, #1e88e5, #42a5f5);
    color: #fff;
    padding: .75rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
}
.mass-borrow-count {
    background: rgba(255,255,255,.25);
    color: #fff;
}
.mass-borrow-body {
    padding: .5rem;
    overflow-y: auto;
    flex: 1;
    background: #f8fafc;
}
.mass-borrow-item {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: .7rem;
    padding: .6rem;
    margin-bottom: .5rem;
}
.mass-borrow-item-title {
    font-weight: 600;
    font-size: .85rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.mass-borrow-item-remove {
    background: none;
    border: none;
    color: #dc3545;
    padding: 0 .2rem;
}
.mass-borrow-item-row {
    display: flex;
    gap: .5rem;
    margin-top: .4rem;
}
.mass-borrow-item-row .form-control, .mass-borrow-item-row .form-select {
    font-size: .82rem;
}
.mass-borrow-footer {
    padding: .6rem 1rem;
    border-top: 1px solid #eee;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .5rem;
}
.mass-borrow-error {
    padding: .5rem 1rem;
    background: #fdecea;
    color: #b02a37;
    font-size: .82rem;
    border-top: 1px solid #f5c6cb;
}
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
(function() {
    let quillEditor = null;

    const editSopModal = document.getElementById('editSopModal');
    const hiddenInput = document.getElementById('sopEditorHidden');
    const editorContainer = document.getElementById('sopEditor');

    // Data SOP existing (dari blade) sebagai HTML awal
    const existingSop = {!! json_encode($lab->sop) !!};

    function initQuill() {
        if (quillEditor) return;

        quillEditor = new Quill(editorContainer, {
            theme: 'snow',
            placeholder: 'Tulis SOP di sini...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['blockquote', 'code-block'],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Isi konten existing
        if (existingSop) {
            quillEditor.clipboard.dangerouslyPasteHTML(existingSop);
        }
    }

    editSopModal.addEventListener('shown.bs.modal', function() {
        setTimeout(initQuill, 150);
    });

    // Saat form disubmit, salin konten HTML ke hidden input
    const form = editSopModal.querySelector('form');
    form.addEventListener('submit', function() {
        if (quillEditor) {
            hiddenInput.value = quillEditor.root.innerHTML;
        } else {
            hiddenInput.value = existingSop || '';
        }
    });
})();
</script>

<script>
(function() {
    // === KERANJANG PEMINJAMAN MASSAL ===
    let items = []; // { key, type, idAlat, idUnit, idBahan, name, satuan, isUnit, spesifikasi[], jumlah, keperluan, idSpekBahan }

    const panel = document.getElementById('massBorrowPanel');
    const itemsContainer = document.getElementById('massBorrowItems');
    const countBadge = document.getElementById('massBorrowCount');
    const errorDiv = document.getElementById('massBorrowError');
    const form = document.getElementById('massBorrowForm');

    function genKey(type, id) { return type + '-' + id; }

    function render() {
        const panelVisible = items.length > 0;
        panel.classList.toggle('d-none', !panelVisible);
        countBadge.textContent = items.length;

        itemsContainer.innerHTML = items.map(function(it, idx) {
            const specOptions = it.spesifikasi && it.spesifikasi.length
                ? it.spesifikasi.map(function(s) {
                    return '<option value="' + s.id + '">' + s.kode + ' - ' + s.nama + ' (Stok ' + s.stok + ')</option>';
                  }).join('')
                : '<option value="">Tidak ada spesifikasi</option>';

            return '' +
            '<div class="mass-borrow-item" data-idx="' + idx + '">' +
                '<div class="mass-borrow-item-title">' +
                    '<span>' + it.name + '</span>' +
                    '<button type="button" class="mass-borrow-item-remove" title="Hapus" data-remove="' + idx + '"><i class="fas fa-times"></i></button>' +
                '</div>' +
                '<div class="mass-borrow-item-row">' +
                    '<input type="number" class="form-control form-control-sm" data-amount="' + idx + '" value="' + it.jumlah + '" min="1" placeholder="Jumlah">' +
                    '<input type="text" class="form-control form-control-sm" data-keperluan="' + idx + '" value="' + (it.keperluan || '') + '" placeholder="Keperluan">' +
                '</div>' +
                (it.type === 'bahan' && it.spesifikasi && it.spesifikasi.length
                    ? '<div class="mass-borrow-item-row"><select class="form-select form-select-sm" data-spek="' + idx + '">' +
                        '<option value="">Pilih Spesifikasi</option>' + specOptions + '</select></div>'
                    : '') +
            '</div>';
        }).join('');
    }

    function findItem(key) {
        return items.findIndex(function(i) { return genKey(i.type, i.type === 'alat' ? (i.isUnit ? i.idUnit : i.idAlat) : i.idBahan) === key; });
    }

    function addFromCheckbox(cb) {
        const type = cb.dataset.type;
        const key = genKey(type, cb.dataset.id);
        const existingIdx = findItem(key);
        if (existingIdx >= 0) { cb.checked = false; return; }

        if (type === 'alat') {
            const isUnit = cb.dataset.tipe === 'unit';
            items.push({
                type: 'alat',
                isUnit: isUnit,
                idAlat: isUnit ? null : parseInt(cb.dataset.id),
                idUnit: isUnit ? parseInt(cb.dataset.id) : null,
                name: cb.dataset.name,
                satuan: cb.dataset.satuan || 'unit',
                jumlah: 1,
                keperluan: ''
            });
        } else {
            let spesifikasi = [];
            try { spesifikasi = JSON.parse(cb.dataset.spesifikasi || '[]'); } catch (e) {}
            items.push({
                type: 'bahan',
                idBahan: parseInt(cb.dataset.id),
                name: cb.dataset.name,
                satuan: cb.dataset.satuan || '',
                spesifikasi: spesifikasi,
                jumlah: 1,
                keperluan: '',
                idSpekBahan: ''
            });
        }
        render();
    }

    function removeItem(idx) {
        items.splice(idx, 1);
        syncCheckboxes();
        render();
    }

    function syncCheckboxes() {
        // Uncheck all alat/bahan checkboxes not in cart
        document.querySelectorAll('.cb-alat').forEach(function(cb) {
            if (cb.disabled) return;
            const key = genKey('alat', cb.dataset.id);
            cb.checked = items.some(function(i) {
                return i.type === 'alat' && genKey('alat', i.isUnit ? i.idUnit : i.idAlat) === key;
            });
        });
        document.querySelectorAll('.cb-bahan').forEach(function(cb) {
            if (cb.disabled) return;
            const key = genKey('bahan', cb.dataset.id);
            cb.checked = items.some(function(i) {
                return i.type === 'bahan' && genKey('bahan', i.idBahan) === key;
            });
        });
    }

    // Event binding pada checkbox
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('cb-alat') || e.target.classList.contains('cb-bahan')) {
            if (e.target.checked) addFromCheckbox(e.target);
            else {
                // uncheck -> hapus dari keranjang
                const key = genKey(e.target.dataset.type, e.target.dataset.id);
                const idx = items.findIndex(function(i) {
                    if (i.type === 'alat') return genKey('alat', i.isUnit ? i.idUnit : i.idAlat) === key;
                    return genKey('bahan', i.idBahan) === key;
                });
                if (idx >= 0) removeItem(idx);
            }
        }
        // select all alat
        if (e.target.classList.contains('check-all-alat')) {
            document.querySelectorAll('.cb-alat').forEach(function(cb) {
                if (!cb.disabled) { cb.checked = e.target.checked; if (e.target.checked) addFromCheckbox(cb); }
            });
            if (!e.target.checked) { items = items.filter(function(i) { return i.type !== 'alat'; }); render(); }
        }
        if (e.target.classList.contains('check-all-bahan')) {
            document.querySelectorAll('.cb-bahan').forEach(function(cb) {
                if (!cb.disabled) { cb.checked = e.target.checked; if (e.target.checked) addFromCheckbox(cb); }
            });
            if (!e.target.checked) { items = items.filter(function(i) { return i.type !== 'bahan'; }); render(); }
        }
    });

    // Value binding (jumlah, keperluan, spesifikasi) via event delegation
    itemsContainer.addEventListener('input', function(e) {
        const idx = parseInt(e.target.dataset.amount ?? e.target.dataset.keperluan, 10);
        if (isNaN(idx) || !items[idx]) return;
        if (e.target.dataset.amount !== undefined) items[idx].jumlah = parseInt(e.target.value) || 1;
        if (e.target.dataset.keperluan !== undefined) items[idx].keperluan = e.target.value;
    });
    itemsContainer.addEventListener('change', function(e) {
        if (e.target.dataset.spek !== undefined) {
            const idx = parseInt(e.target.dataset.spek, 10);
            if (!isNaN(idx) && items[idx]) items[idx].idSpekBahan = e.target.value;
        }
    });

    // Hapus item
    itemsContainer.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-remove]');
        if (btn) removeItem(parseInt(btn.dataset.remove, 10));
    });

    // Bersihkan
    document.getElementById('massBorrowClear').addEventListener('click', function() {
        items = [];
        syncCheckboxes();
        render();
    });
    document.getElementById('massBorrowClose').addEventListener('click', function() {
        items = [];
        syncCheckboxes();
        render();
    });

    // Simpan
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        errorDiv.classList.add('d-none');

        const data = items.map(function(it) {
            return {
                type: it.type,
                id_alat: it.type === 'alat' ? it.idAlat : null,
                id_unit_alat: it.type === 'alat' ? it.idUnit : null,
                id_bahan: it.type === 'bahan' ? it.idBahan : null,
                id_spesifikasi_bahan: it.type === 'bahan' ? (it.idSpekBahan || null) : null,
                jumlah: it.jumlah || 1,
                keperluan: it.keperluan || ''
            };
        });

        const body = new URLSearchParams();
        body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        data.forEach(function(it, i) {
            body.append('items[' + i + '][type]', it.type);
            body.append('items[' + i + '][id_alat]', it.id_alat || '');
            body.append('items[' + i + '][id_unit_alat]', it.id_unit_alat || '');
            body.append('items[' + i + '][id_bahan]', it.id_bahan || '');
            body.append('items[' + i + '][id_spesifikasi_bahan]', it.id_spesifikasi_bahan || '');
            body.append('items[' + i + '][jumlah]', it.jumlah);
            body.append('items[' + i + '][keperluan]', it.keperluan);
        });

        const submitBtn = form.querySelector('button[type=submit]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

        fetch(form.action, { method: 'POST', headers: { 'Accept': 'application/json' }, body: body })
            .then(function(res) { return res.json().catch(function(){ return null; }); })
            .then(function(data) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Simpan Massal';
                if (data && data.success) {
                    window.location.reload();
                } else {
                    errorDiv.textContent = (data && data.message) ? data.message : 'Terjadi kesalahan.';
                    errorDiv.classList.remove('d-none');
                }
            })
            .catch(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Simpan Massal';
                errorDiv.textContent = 'Terjadi kesalahan jaringan.';
                errorDiv.classList.remove('d-none');
            });
    });
})();
</script>
@endpush
