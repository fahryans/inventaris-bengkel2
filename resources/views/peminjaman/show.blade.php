@extends('layouts.admin')

@section('title', 'Detail Peminjaman')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}">Data Peminjaman</a></li>
            <li class="breadcrumb-item active">Detail Peminjaman</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        {{ $peminjaman->alat?->nama_alat ?? $peminjaman->unitAlat?->alat?->nama_alat ?? 'Unknown' }}
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Peminjam:</strong></p>
                            <p>{{ $peminjaman->userPeminjam->nama }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Keperluan:</strong></p>
                            <p>{{ $peminjaman->keperluan }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Waktu Peminjaman:</strong></p>
                            <p>{{ $peminjaman->waktu_peminjaman->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Waktu Pengembalian (Rencana):</strong></p>
                            <p>{{ $peminjaman->waktu_pengembalian?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Kondisi Saat Peminjaman:</strong></p>
                            <p><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $peminjaman->kondisi_saat_peminjaman)) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong></p>
                            <p>
                                <span class="badge bg-{{ $peminjaman->status == 'terpinjam' ? 'warning' : 'success' }}">
                                    {{ ucfirst(str_replace('_', ' ', $peminjaman->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($peminjaman->waktu_kembali_aktual)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Waktu Kembali Aktual:</strong></p>
                                <p>{{ $peminjaman->waktu_kembali_aktual->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Kondisi Saat Pengembalian:</strong></p>
                                <p><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $peminjaman->kondisi_saat_pengembalian)) }}</span></p>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        @can('return', $peminjaman)
                        @if($peminjaman->status == 'terpinjam')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#returnModal">
                                <i class="fas fa-undo"></i> Kembalikan
                            </button>
                        @endif
                        @endcan
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Status Peminjaman</h6>
                </div>
                <div class="card-body">
                    <p><strong>Tipe Alat:</strong><br><span class="badge bg-secondary">{{ $peminjaman->id_alat ? 'Agregat' : 'Unit' }}</span></p>
                    @if($peminjaman->isOverdue())
                        <p><strong>Status:</strong><br><span class="badge bg-danger">Overdue {{ $peminjaman->getDaysOverdue() }} hari</span></p>
                    @else
                        <p><strong>Status:</strong><br><span class="badge bg-success">Normal</span></p>
                    @endif
                    @if($peminjaman->id_unit_alat)
                        <p><strong>Kode Inventaris:</strong><br>{{ $peminjaman->unitAlat->kode_inventaris }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($peminjaman->status == 'terpinjam')
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnForm" action="{{ route('peminjaman.return', $peminjaman) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="returnModalLabel">
                        <i class="fas fa-undo"></i> Konfirmasi Pengembalian
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Alat:</strong> {{ $peminjaman->alat?->nama_alat ?? $peminjaman->unitAlat?->alat?->nama_alat ?? 'Unknown' }}<br>
                        <strong>Peminjam:</strong> {{ $peminjaman->userPeminjam->nama }}
                    </div>

                    <div class="mb-3">
                        <label for="waktu_kembali_aktual" class="form-label">Waktu Kembali Aktual <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="waktu_kembali_aktual" id="waktu_kembali_aktual"
                               class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="kondisi_saat_pengembalian" class="form-label">Kondisi Saat Pengembalian <span class="text-danger">*</span></label>
                        <select name="kondisi_saat_pengembalian" id="kondisi_saat_pengembalian" class="form-select" required>
                            <option value="">Pilih Kondisi</option>
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                    </div>

                    <div id="returnError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnReturnSubmit">
                        <i class="fas fa-check"></i> Konfirmasi Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('returnForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('btnReturnSubmit');
    const errorDiv = document.getElementById('returnError');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    errorDiv.classList.add('d-none');

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        } else {
            return response.json().then(data => {
                throw new Error(data.message || 'Gagal mengembalikan peminjaman.');
            });
        }
    })
    .catch(err => {
        errorDiv.textContent = err.message;
        errorDiv.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Konfirmasi Pengembalian';
    });
});
</script>
@endif
