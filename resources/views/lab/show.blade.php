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
                        <i class="fas fa-screwdriver-wrench me-1"></i> Daftar Alat ({{ $lab->alat->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($lab->alat->count())
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
                                    @foreach($lab->alat as $alat)
                                        <tr>
                                            <td>{{ $alat->nama_alat }}</td>
                                            <td>{{ $alat->merek ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $alat->tipe_pelacakan === 'agregat' ? 'primary' : 'info' }}">
                                                    {{ ucfirst($alat->tipe_pelacakan) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($alat->tipe_pelacakan === 'agregat')
                                                    {{ $alat->getAvailableQuantity() }} unit
                                                @else
                                                    {{ $alat->unitAlat->where('status', 'tersedia')->count() }} / {{ $alat->unitAlat->count() }} tersedia
                                                @endif
                                            </td>
                                            <td>
                                                @can('create', \App\Models\PeminjamanAlat::class)
                                                    @if($alat->tipe_pelacakan === 'agregat' && $alat->getAvailableQuantity() > 0)
                                                        <button type="button" class="btn btn-sm btn-primary btn-pinjam"
                                                                data-tipe="agregat" data-id="{{ $alat->id }}" data-name="{{ $alat->nama_alat }}">
                                                            <i class="fas fa-handshake"></i> Pinjam
                                                        </button>
                                                    @elseif($alat->tipe_pelacakan === 'unit' && $alat->unitAlat->where('status', 'tersedia')->count() > 0)
                                                        @php $availableUnit = $alat->unitAlat->firstWhere('status', 'tersedia'); @endphp
                                                        <button type="button" class="btn btn-sm btn-primary btn-pinjam"
                                                                data-tipe="unit" data-id="{{ $availableUnit->id }}" data-name="{{ $alat->nama_alat }} ({{ $availableUnit->kode_inventaris }})">
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
                        <i class="fas fa-flask me-1"></i> Daftar Bahan ({{ $lab->bahan->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($lab->bahan->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Bahan</th>
                                        <th>Merek</th>
                                        <th>Stok</th>
                                        <th>Satuan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lab->bahan as $bahan)
                                        <tr>
                                            <td>{{ $bahan->nama_bahan }}</td>
                                            <td>{{ $bahan->merek ?? '-' }}</td>
                                            <td>{{ $bahan->stok_saat_ini }}</td>
                                            <td>{{ $bahan->satuan }}</td>
                                            <td>
                                                @if($bahan->isStokMenipis())
                                                    <span class="badge bg-danger">Stok Menipis</span>
                                                @else
                                                    <span class="badge bg-success">Aman</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endsection
