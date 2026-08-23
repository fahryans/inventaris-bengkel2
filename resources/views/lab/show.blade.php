@extends('layouts.admin')

@section('title', $lab->nama_labor)

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                    @if($lab->sop)
                        <small class="text-muted">SOP: {{ $lab->sop }}</small>
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
                                        <th>Nama Alat</th>
                                        <th>Merek</th>
                                        <th>Tipe</th>
                                        <th>Stok/Unit</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alat as $item)
                                        <tr>
                                            <td>{{ $item->nama_alat }}</td>
                                            <td>{{ $item->merek ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->tipe_pelacakan === 'agregat' ? 'primary' : 'info' }}">
                                                    {{ ucfirst($item->tipe_pelacakan) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->tipe_pelacakan === 'agregat')
                                                    {{ $item->getAvailableQuantity() }} unit
                                                @else
                                                    {{ $item->unitAlat->where('status', 'tersedia')->count() }} / {{ $item->unitAlat->count() }} tersedia
                                                @endif
                                            </td>
                                            <td>
                                                @can('create', \App\Models\PeminjamanAlat::class)
                                                    @if($item->tipe_pelacakan === 'agregat' && $item->getAvailableQuantity() > 0)
                                                        <button type="button" class="btn btn-sm btn-primary btn-pinjam"
                                                                data-tipe="agregat" data-id="{{ $item->id }}" data-name="{{ $item->nama_alat }}">
                                                            <i class="fas fa-handshake"></i> Pinjam
                                                        </button>
                                                    @elseif($item->tipe_pelacakan === 'unit' && $item->unitAlat->where('status', 'tersedia')->count() > 0)
                                                        @php $availableUnit = $item->unitAlat->firstWhere('status', 'tersedia'); @endphp
                                                        <button type="button" class="btn btn-sm btn-primary btn-pinjam"
                                                                data-tipe="unit" data-id="{{ $availableUnit->id }}" data-name="{{ $item->nama_alat }} ({{ $availableUnit->kode_inventaris }})">
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
                                        <th>Nama Bahan</th>
                                        <th>Merek</th>
                                        <th>Stok</th>
                                        <th>Satuan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bahan as $item)
                                        @php
                                            $stok = \App\Models\PengadaanBahan::where('id_bahan', $item->id)->where('stok_tersisa_batch', '>', 0)->whereNotNull('tanggal_masuk')->sum('stok_tersisa_batch');
                                            $batches = \App\Models\PengadaanBahan::where('id_bahan', $item->id)->where('stok_tersisa_batch', '>', 0)->whereNotNull('tanggal_masuk')->get();
                                        @endphp
                                        <tr>
                                            <td>{{ $item->nama_bahan }}</td>
                                            <td>{{ $item->merek ?? '-' }}</td>
                                            <td>{{ $stok }}</td>
                                            <td>{{ $item->satuan }}</td>
                                            <td>
                                                @if($item->isStokMenipis())
                                                    <span class="badge bg-danger">Stok Menipis</span>
                                                @else
                                                    <span class="badge bg-success">Aman</span>
                                                @endif
                                            </td>
                                            <td>
                                                @can('create', \App\Models\PemakaianBahan::class)
                                                    @if($stok > 0)
                                                        <button type="button" class="btn btn-sm btn-success btn-pakai-bahan"
                                                                data-id="{{ $item->id }}"
                                                                data-name="{{ $item->nama_bahan }}"
                                                                data-stok="{{ $stok }}"
                                                                data-satuan="{{ $item->satuan }}"
                                                                data-batches='@json($batches->map(fn($b) => ["id" => $b->id, "label" => $b->supplier." (".$b->stok_tersisa_batch." ".$item->satuan.")"]))'>
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
                    <div class="mb-3">
                        <label for="modalKeperluan" class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="keperluan" id="modalKeperluan" class="form-control" required
                               placeholder="Contoh: Praktikum Jaringan" maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="modalWaktuKembali" class="form-label">Tanggal Pengembalian <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="waktu_pengembalian" id="modalWaktuKembali" class="form-control" required>
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
                    <div class="mb-3">
                        <label for="lab_id_pengadaan_bahan" class="form-label">Batch Pengadaan <span class="text-danger">*</span></label>
                        <select name="id_pengadaan_bahan" id="lab_id_pengadaan_bahan" class="form-select" required>
                            <option value="">-- Pilih Bahan Dulu --</option>
                        </select>
                        <small class="text-muted" id="lab_stokInfo">Stok tersedia: -</small>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === PINJAM ALAT ===
    const modal = new bootstrap.Modal(document.getElementById('modalPinjam'));
    const form = document.getElementById('formQuickPinjam');
    const inputTipe = document.getElementById('modalTipe');
    const inputIdAlat = document.getElementById('modalIdAlat');
    const inputIdUnit = document.getElementById('modalIdUnit');
    const inputName = document.getElementById('modalAlatName');
    const inputKeperluan = document.getElementById('modalKeperluan');
    const inputWaktuKembali = document.getElementById('modalWaktuKembali');

    function setDefaultReturnDate() {
        const now = new Date();
        now.setDate(now.getDate() + 7);
        now.setMinutes(0, 0, 0);
        const offset = now.getTimezoneOffset();
        const local = new Date(now.getTime() - (offset * 60 * 1000));
        inputWaktuKembali.value = local.toISOString().slice(0, 16);
    }

    document.querySelectorAll('.btn-pinjam').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tipe = this.dataset.tipe;
            const id = this.dataset.id;
            const name = this.dataset.name;

            inputTipe.value = tipe;
            inputName.textContent = name;
            inputKeperluan.value = '';
            setDefaultReturnDate();

            if (tipe === 'agregat') {
                inputIdAlat.value = id;
                inputIdUnit.value = '';
            } else {
                inputIdAlat.value = '';
                inputIdUnit.value = id;
            }

            modal.show();
            setTimeout(() => inputKeperluan.focus(), 300);
        });
    });

    // === PAKAI BAHAN ===
    var bahanModal = new bootstrap.Modal(document.getElementById('pakaiBahanModal'));
    var bahanForm = document.getElementById('pakaiBahanForm');
    var bahanName = document.getElementById('pakaiBahanName');
    var batchSelect = document.getElementById('lab_id_pengadaan_bahan');
    var stokInfo = document.getElementById('lab_stokInfo');

    document.querySelectorAll('.btn-pakai-bahan').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var name = this.dataset.name;
            var stok = this.dataset.stok;
            var satuan = this.dataset.satuan;
            var batches = JSON.parse(this.dataset.batches || '[]');

            document.getElementById('lab_hidden_id_bahan').value = id;
            document.getElementById('lab_waktu_pemakaian').value = new Date().toISOString().slice(0, 16);
            bahanName.textContent = name + ' (Stok: ' + stok + ' ' + satuan + ')';
            stokInfo.textContent = 'Stok tersedia: ' + stok + ' ' + satuan;

            batchSelect.innerHTML = '<option value="">-- Pilih Batch --</option>';
            batches.forEach(function(b) {
                var opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.label;
                batchSelect.appendChild(opt);
            });

            document.getElementById('lab_jumlah_pengambilan').value = '';
            document.getElementById('lab_keperluan_pakai').value = '';
            document.getElementById('lab_pakaiBahanError').classList.add('d-none');

            bahanModal.show();
        });
    });

    bahanForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('lab_btnPakaiBahanSubmit');
        var errorDiv = document.getElementById('lab_pakaiBahanError');

        document.getElementById('lab_waktu_pemakaian').value = new Date().toISOString().slice(0, 16);

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
