@extends('layouts.admin')

@section('title', 'Detail Pemakaian Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pemakaian_bahan.index') }}">Data Pemakaian Bahan</a></li>
            <li class="breadcrumb-item active">Detail Pemakaian</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-[#5b202f] text-[#f5f0e9]">
                    <h5 class="mb-0">{{ $pemakaian->bahan->nama_bahan ?? '-' }}</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Keperluan:</strong></p>
                            <p>{{ $pemakaian->keperluan }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Waktu Pemakaian:</strong></p>
                            <p>{{ $pemakaian->waktu_pemakaian?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p><strong>Jumlah Pengambilan:</strong></p>
                            <p>{{ $pemakaian->jumlah_pengambilan }} {{ $pemakaian->bahan->satuan ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Jumlah Terpakai:</strong></p>
                            <p>{{ $pemakaian->jumlah_terpakai }} {{ $pemakaian->bahan->satuan ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Jumlah Pengembalian:</strong></p>
                            <p>{{ $pemakaian->jumlah_pengembalian }} {{ $pemakaian->bahan->satuan ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Batch Pengadaan:</strong></p>
                            <p>{{ $pemakaian->pengadaanBahan->supplier ?? '-' }} ({{ $pemakaian->pengadaanBahan->tanggal_pengadaan?->format('d/m/Y') ?? '-' }})</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status Verifikasi Pemakaian:</strong></p>
                            <p>
                                @if($pemakaian->id_user_verifikasi)
                                    <span class="badge bg-success">Terverifikasi oleh {{ $pemakaian->userVerifikasi->nama ?? '-' }}</span>
                                @else
                                    <span class="badge bg-warning">Belum Diverifikasi</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p><strong>Status Pengembalian:</strong></p>
                            <p>
                                @if($pemakaian->status_pengembalian === 'pending')
                                    <span class="badge bg-warning">Menunggu Verifikasi</span>
                                @elseif($pemakaian->status_pengembalian === 'verified')
                                    <span class="badge bg-success">Diverifikasi</span>
                                @elseif($pemakaian->status_pengembalian === 'rejected')
                                    <span class="badge bg-danger">Ditolak</span>
                                @elseif(!is_null($pemakaian->jumlah_pengembalian))
                                    <span class="badge bg-secondary">Sudah Dikembalikan</span>
                                @else
                                    <span class="badge bg-secondary">Belum Dikembalikan</span>
                                @endif
                                @if($pemakaian->waktu_pengembalian)
                                    <small class="text-muted d-block">Disubmit: {{ $pemakaian->waktu_pengembalian->format('d-m-Y H:i') }}</small>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @can('verifyReturn', $pemakaian)
                        <div class="mb-3">
                            <form action="{{ route('pemakaian_bahan.verify_return', $pemakaian) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Verifikasi pengembalian bahan ini? Stok akan dikembalikan ke inventory.')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i> Verifikasi Pengembalian
                                </button>
                            </form>
                            <form action="{{ route('pemakaian_bahan.reject_return', $pemakaian) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Tolak pengembalian bahan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-times"></i> Tolak Pengembalian
                                </button>
                            </form>
                        </div>
                        @endcan
                        @can('return', $pemakaian)
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#returnBahanModal">
                                <i class="fas fa-undo"></i> Kembalikan Sisa
                            </button>
                        @endcan
                        @can('verify', $pemakaian)
                        @if(!$pemakaian->id_user_verifikasi)
                            <form action="{{ route('pemakaian_bahan.verify', $pemakaian) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Verifikasi
                                </button>
                            </form>
                        @endif
                        @endcan
                        @can('update', $pemakaian)
                        <a href="{{ route('pemakaian_bahan.edit', $pemakaian) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        @can('delete', $pemakaian)
                        <form action="{{ route('pemakaian_bahan.destroy', $pemakaian) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endcan
                        <a href="{{ route('pemakaian_bahan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Informasi Bahan</h6>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong><br>{{ $pemakaian->bahan->nama_bahan ?? '-' }}</p>
                    <p><strong>Kategori:</strong><br><span class="badge bg-secondary">{{ $pemakaian->bahan->kategori->nama_kategori ?? '-' }}</span></p>
                    <p><strong>Lab:</strong><br>{{ $pemakaian->bahan->laboratorium->nama_labor ?? '-' }}</p>
                    <p><strong>Merek:</strong><br>{{ $pemakaian->pengadaanBahan->merek ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@can('return', $pemakaian)
<div class="modal fade" id="returnBahanModal" tabindex="-1" aria-labelledby="returnBahanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnBahanForm" action="{{ route('pemakaian_bahan.return', $pemakaian) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="returnBahanModalLabel">
                        <i class="fas fa-undo"></i> Kembalikan Sisa Bahan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Bahan:</strong> {{ $pemakaian->bahan->nama_bahan ?? '-' }}<br>
                        <strong>Jumlah Diambil:</strong> {{ $pemakaian->jumlah_pengambilan }} {{ $pemakaian->bahan->satuan ?? '-' }}
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_terpakai" class="form-label">Jumlah Terpakai <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_terpakai" id="jumlah_terpakai"
                               class="form-control" value="{{ $pemakaian->jumlah_terpakai ?? $pemakaian->jumlah_pengambilan }}"
                               min="1" max="{{ $pemakaian->jumlah_pengambilan }}" required>
                        <small class="text-muted">Berapa yang benar-benar terpakai</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sisa (Otomatis)</label>
                        <input type="text" id="sisaDisplay" class="form-control" readonly
                               value="{{ ($pemakaian->jumlah_pengambilan - ($pemakaian->jumlah_terpakai ?? $pemakaian->jumlah_pengambilan)) }} {{ $pemakaian->bahan->satuan ?? '-' }}">
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_pengembalian" class="form-label">Jumlah Dikembalikan <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_pengembalian" id="jumlah_pengembalian"
                               class="form-control" value="0"
                               min="0" max="{{ $pemakaian->jumlah_pengambilan }}" required>
                        <small class="text-muted">Berapa yang dikembalikan ke stok</small>
                    </div>

                    <div id="returnBahanError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnReturnBahanSubmit">
                        <i class="fas fa-check"></i> Konfirmasi Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('jumlah_terpakai')?.addEventListener('input', function() {
    const ambil = {{ $pemakaian->jumlah_pengambilan }};
    const pakai = parseInt(this.value) || 0;
    const sisa = ambil - pakai;
    const satuan = '{{ $pemakaian->bahan->satuan ?? "" }}';
    document.getElementById('sisaDisplay').value = sisa + ' ' + satuan;
    const inputKembali = document.getElementById('jumlah_pengembalian');
    inputKembali.max = sisa;
    if (parseInt(inputKembali.value) > sisa) inputKembali.value = sisa;
});

document.getElementById('returnBahanForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('btnReturnBahanSubmit');
    const errorDiv = document.getElementById('returnBahanError');

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
                throw new Error(data.message || Object.values(data.errors || {}).flat().join(', '));
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
@endcan
