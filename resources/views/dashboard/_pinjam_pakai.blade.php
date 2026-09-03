<hr class="my-5">

{{-- Section tambahan ala dashboard mahasiswa: card labor + peminjaman & pemakaian --}}
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">
            <i class="fas fa-chart-pie me-2"></i> Peminjaman & Pemakaian Bahan per Laboratorium
        </h4>
    </div>
</div>

{{-- Statistik --}}
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-primary text-uppercase mb-1 small font-weight-bold">Peminjaman Aktif</div>
                <div class="h3 mb-0">{{ $pkActiveCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-success text-uppercase mb-1 small font-weight-bold">Total Laboratorium</div>
                <div class="h3 mb-0">{{ $pkLabs->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-info text-uppercase mb-1 small font-weight-bold">Riwayat Peminjaman</div>
                <div class="h3 mb-0">{{ $pkRiwayatCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-warning text-uppercase mb-1 small font-weight-bold">Total Alat</div>
                <div class="h3 mb-0">{{ $pkLabs->sum('alat_count') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Card Laboratorium --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">Daftar Laboratorium</h5>
    </div>

    @forelse($pkLabs as $lab)
        <div class="col-md-4 mb-4">
            <a href="{{ route('lab.show', $lab) }}" class="text-decoration-none">
                <div class="card shadow h-100 lab-card">
                    @if($lab->gambar)
                        <img src="{{ asset('storage/' . $lab->gambar) }}"
                             class="card-img-top"
                             alt="{{ $lab->nama_labor }}"
                             style="height: 180px; object-fit: cover;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="card-img-top bg-primary text-white d-none align-items-center justify-content-center" style="height: 180px;">
                            <i class="fas fa-building fa-3x"></i>
                        </div>
                    @else
                        <div class="card-img-top bg-primary text-white d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="fas fa-building fa-3x"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title mb-1">{{ $lab->nama_labor }}</h5>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $lab->lokasi }}</small>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <div class="border rounded py-2">
                                    <div class="h5 mb-0 text-primary">{{ $lab->alat_count }}</div>
                                    <small class="text-muted">Alat</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded py-2">
                                    <div class="h5 mb-0 text-success">{{ $lab->bahan_count }}</div>
                                    <small class="text-muted">Bahan</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">Klik untuk melihat detail →</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">Belum ada laboratorium yang tersedia.</div>
        </div>
    @endforelse
</div>

@push('css')
<style>
    .lab-card {
        height: 100%;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .lab-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }
    .lab-card .card-img-top {
        transition: transform 0.3s ease;
    }
    .lab-card:hover .card-img-top {
        transform: scale(1.03);
    }
</style>
@endpush

{{-- Peminjaman Aktif --}}
@if($pkPeminjaman->count())
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-handshake me-1"></i> Peminjaman Alat Aktif
                </h6>
                <a href="{{ route('peminjaman.index') }}?status=terpinjam" class="btn btn-sm btn-outline-primary">Lihat Semua →</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Alat/Unit</th>
                                <th>Peminjam</th>
                                <th>Keperluan</th>
                                <th>Dipinjam Sejak</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pkPeminjaman as $peminjaman)
                                <tr>
                                    <td>{{ $peminjaman->equipment_name }}</td>
                                    <td>{{ $peminjaman->userPeminjam?->nama ?? '-' }}</td>
                                    <td>{{ $peminjaman->keperluan }}</td>
                                    <td>{{ $peminjaman->waktu_peminjaman?->format('d-m-Y H:i') }}</td>
                                    <td>
                                        @if($peminjaman->waktu_pengembalian)
                                            <small class="{{ method_exists($peminjaman, 'isOverdue') && $peminjaman->isOverdue() ? 'text-danger fw-bold' : 'text-muted' }}">
                                                <i class="fas fa-{{ method_exists($peminjaman, 'isOverdue') && $peminjaman->isOverdue() ? 'exclamation-triangle' : 'clock' }}"></i>
                                                {{ $peminjaman->waktu_pengembalian->format('d-m-Y H:i') }}
                                            </small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-warning">Terpinjam</span></td>
                                    <td>
                                        <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Pemakaian Bahan Perlu Diverifikasi --}}
@if($pkPendingPemakaian->count())
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-flask me-1"></i> Pemakaian Bahan Perlu Diverifikasi
                </h6>
                <a href="{{ route('pemakaian_bahan.index') }}?verified=0" class="btn btn-sm btn-outline-warning">Lihat Semua →</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pemakai</th>
                                <th>Bahan</th>
                                <th>Supplier</th>
                                <th>Diambil</th>
                                <th>Keperluan</th>
                                <th>Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pkPendingPemakaian as $pemakaian)
                                <tr>
                                    <td>{{ $pemakaian->userPemakai->nama ?? '-' }}</td>
                                    <td>{{ $pemakaian->bahan->nama_bahan ?? '-' }}</td>
                                    <td>{{ $pemakaian->pengadaanBahan->supplier ?? '-' }}</td>
                                    <td>{{ $pemakaian->jumlah_pengambilan }} {{ $pemakaian->bahan->satuan ?? '-' }}</td>
                                    <td>{{ $pemakaian->keperluan }}</td>
                                    <td><small class="text-muted">{{ $pemakaian->created_at?->format('d-m-Y H:i') }}</small></td>
                                    <td>
                                        <a href="{{ route('pemakaian_bahan.show', $pemakaian) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('verify', $pemakaian)
                                        <form action="{{ route('pemakaian_bahan.verify', $pemakaian) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Verifikasi pemakaian bahan ini?')">
                                                <i class="fas fa-check"></i> Verifikasi
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($pkPendingReturns->count())
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-undo me-1"></i> Pengembalian Menunggu Verifikasi
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pemakai</th>
                                <th>Bahan</th>
                                <th>Supplier</th>
                                <th>Diambil</th>
                                <th>Dikembalikan</th>
                                <th>Waktu Return</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pkPendingReturns as $pemakaian)
                                <tr>
                                    <td>{{ $pemakaian->userPemakai->nama ?? '-' }}</td>
                                    <td>{{ $pemakaian->bahan->nama_bahan ?? '-' }}</td>
                                    <td>{{ $pemakaian->pengadaanBahan->supplier ?? '-' }}</td>
                                    <td>{{ $pemakaian->jumlah_pengambilan }} {{ $pemakaian->bahan->satuan ?? '-' }}</td>
                                    <td>{{ $pemakaian->jumlah_pengembalian }} {{ $pemakaian->bahan->satuan ?? '-' }}</td>
                                    <td><small class="text-muted">{{ $pemakaian->waktu_pengembalian?->format('d-m-Y H:i') ?? '-' }}</small></td>
                                    <td>
                                        <a href="{{ route('pemakaian_bahan.show', $pemakaian) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('verifyReturn', $pemakaian)
                                        <form action="{{ route('pemakaian_bahan.verify_return', $pemakaian) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Verifikasi pengembalian ini? Stok akan dikembalikan ke inventory.')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                        </form>
                                        @endcan
                                        @can('rejectReturn', $pemakaian)
                                        <form action="{{ route('pemakaian_bahan.reject_return', $pemakaian) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Tolak pengembalian ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Peminjaman Alat Saya (user yang login) --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-handshake me-1"></i> Peminjaman Alat Saya
                </h6>
                <a href="{{ route('laporan.saya') }}" class="btn btn-sm btn-outline-primary">Lihat Semua →</a>
            </div>
            <div class="card-body">
                @if($pkMyPeminjaman->count())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Alat/Unit</th>
                                <th>Laboratorium</th>
                                <th>Keperluan</th>
                                <th>Dipinjam Sejak</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pkMyPeminjaman as $peminjaman)
                                <tr>
                                    <td>{{ $peminjaman->equipment_name }}</td>
                                    <td>{{ $peminjaman->alat?->laboratorium?->nama_labor ?? $peminjaman->unitAlat?->alat?->laboratorium?->nama_labor ?? '-' }}</td>
                                    <td>{{ $peminjaman->keperluan }}</td>
                                    <td>{{ $peminjaman->waktu_peminjaman?->format('d-m-Y H:i') }}</td>
                                    <td>
                                        @if($peminjaman->waktu_pengembalian)
                                            <small class="{{ method_exists($peminjaman, 'isOverdue') && $peminjaman->isOverdue() ? 'text-danger fw-bold' : 'text-muted' }}">
                                                <i class="fas fa-{{ method_exists($peminjaman, 'isOverdue') && $peminjaman->isOverdue() ? 'exclamation-triangle' : 'clock' }}"></i>
                                                {{ $peminjaman->waktu_pengembalian->format('d-m-Y H:i') }}
                                            </small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $peminjaman->status === 'terpinjam' ? 'warning' : ($peminjaman->status === 'sudah_dikembalikan' ? 'success' : 'danger') }}">
                                            {{ ucfirst(str_replace('_', ' ', $peminjaman->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-muted mb-0">Anda belum pernah meminjam alat.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Pemakaian Bahan Saya (user yang login) --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-flask me-1"></i> Pemakaian Bahan Saya
                </h6>
                <a href="{{ route('pemakaian_bahan.index') }}" class="btn btn-sm btn-outline-success">Lihat Semua →</a>
            </div>
            <div class="card-body">
                @if($pkMyPemakaian->count())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Bahan</th>
                                <th>Laboratorium</th>
                                <th>Supplier</th>
                                <th>Diambil</th>
                                <th>Terpakai</th>
                                <th>Keperluan</th>
                                <th>Waktu</th>
                                <th>Verifikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pkMyPemakaian as $pemakaian)
                                <tr>
                                    <td>{{ $pemakaian->bahan->nama_bahan ?? '-' }}</td>
                                    <td>{{ $pemakaian->bahan->laboratorium?->nama_labor ?? '-' }}</td>
                                    <td>{{ $pemakaian->pengadaanBahan->supplier ?? '-' }}</td>
                                    <td>{{ $pemakaian->jumlah_pengambilan }} {{ $pemakaian->bahan->satuan ?? '-' }}</td>
                                    <td>{{ $pemakaian->jumlah_terpakai }}</td>
                                    <td>{{ $pemakaian->keperluan }}</td>
                                    <td><small class="text-muted">{{ $pemakaian->created_at?->format('d-m-Y H:i') }}</small></td>
                                    <td>
                                        @if($pemakaian->id_user_verifikasi)
                                            <span class="badge bg-success">Terverifikasi</span>
                                        @else
                                            <span class="badge bg-secondary">Menunggu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('pemakaian_bahan.show', $pemakaian) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-muted mb-0">Anda belum pernah memakai bahan.</p>
                @endif
            </div>
        </div>
    </div>
</div>
