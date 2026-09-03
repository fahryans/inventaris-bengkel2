@extends('layouts.admin')

@section('title', 'Dashboard Kadep')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 d-inline-block">Dashboard Kepala Departemen</h1>
            <p class="text-muted">Laporan Sistem Inventaris</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small font-weight-bold">Total Alat</div>
                    <div class="h3 mb-0">{{ $totalAlat }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Total Bahan</div>
                    <div class="h3 mb-0">{{ $totalBahan }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Total Lab</div>
                    <div class="h3 mb-0">{{ $totalLaboratorium }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small font-weight-bold">Total Peminjaman</div>
                    <div class="h3 mb-0">{{ $totalPeminjaman }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small font-weight-bold">Bahan dengan Stok Minimum</div>
                    <div class="h3 mb-0">{{ $lowStockBahan }}</div>
                    <a href="{{ route('bahan.index', ['stock_status' => 'low']) }}" class="small text-muted">Lihat Detail &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-secondary text-uppercase mb-1 small font-weight-bold">Akses Cepat</div>
                    <div class="mt-3">
                        <a href="{{ route('alat.index') }}" class="btn btn-sm btn-info">Lihat Alat</a>
                        <a href="{{ route('bahan.index') }}" class="btn btn-sm btn-warning">Lihat Bahan</a>
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-primary">Lihat Peminjaman</a>
                        <a href="{{ route('pemakaian_bahan.index') }}" class="btn btn-sm btn-success">Pemakaian Bahan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Peminjaman Tahun {{ now()->year }}</h6>
                </div>
                <div class="card-body">
                    @if($peminjamPerBulan->count())
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="peminjamChart"></canvas>
                        </div>
                    @else
                        <p class="text-muted">Data peminjaman belum tersedia</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($myPemakaianBahan->count())
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-flask me-1"></i> Pemakaian Bahan Saya
                    </h6>
                    <a href="{{ route('pemakaian_bahan.index') }}" class="btn btn-sm btn-outline-success">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bahan</th>
                                    <th>Supplier</th>
                                    <th>Diambil</th>
                                    <th>Keperluan</th>
                                    <th>Waktu</th>
                                    <th>Status Pengembalian</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myPemakaianBahan as $pemakaian)
                                    <tr>
                                        <td>{{ $pemakaian->bahan->nama_bahan ?? '-' }}</td>
                                        <td>{{ $pemakaian->pengadaanBahan->supplier ?? '-' }}</td>
                                        <td>{{ $pemakaian->jumlah_pengambilan }} {{ $pemakaian->bahan->satuan ?? '-' }}</td>
                                        <td>{{ $pemakaian->keperluan }}</td>
                                        <td><small class="text-muted">{{ $pemakaian->created_at->format('d-m-Y H:i') }}</small></td>
                                        <td>
                                            @if($pemakaian->status_pengembalian === 'pending')
                                                <span class="badge bg-warning">Menunggu Verifikasi</span>
                                            @elseif($pemakaian->status_pengembalian === 'verified')
                                                <span class="badge bg-success">Diverifikasi</span>
                                            @elseif($pemakaian->status_pengembalian === 'rejected')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @else
                                                <span class="badge bg-secondary">Belum Dikembalikan</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('return', $pemakaian)
                                            <button type="button" class="btn btn-sm btn-warning btn-return-bahan-kadep"
                                                    data-id="{{ $pemakaian->id }}"
                                                    data-name="{{ $pemakaian->bahan->nama_bahan ?? '-' }}"
                                                    data-ambil="{{ $pemakaian->jumlah_pengambilan }}"
                                                    data-satuan="{{ $pemakaian->bahan->satuan ?? '-' }}">
                                                <i class="fas fa-undo"></i> Kembalikan Sisa
                                            </button>
                                            @endcan
                                            <a href="{{ route('pemakaian_bahan.show', $pemakaian) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
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

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Akses Laporan</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="{{ route('alat.index') }}">Daftar Alat Lengkap</a></li>
                        <li class="list-group-item"><a href="{{ route('bahan.index') }}">Daftar Bahan Lengkap</a></li>
                        <li class="list-group-item"><a href="{{ route('laboratorium.index') }}">Daftar Laboratorium</a></li>
                        <li class="list-group-item"><a href="{{ route('users.index') }}">Daftar Pengguna</a></li>
                        <li class="list-group-item"><a href="{{ route('peminjaman.index') }}">Riwayat Peminjaman</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ringkasan Sistem</h6>
                </div>
                <div class="card-body">
                    <p><strong>Total Alat Terdaftar:</strong> {{ $totalAlat }} unit</p>
                    <p><strong>Total Bahan Terdaftar:</strong> {{ $totalBahan }} item</p>
                    <p><strong>Total Laboratorium:</strong> {{ $totalLaboratorium }} lab</p>
                    <p><strong>Total Transaksi Peminjaman:</strong> {{ $totalPeminjaman }} transaksi</p>
                    <p><strong>Bahan dengan Stok Kritis:</strong> {{ $lowStockBahan }} item</p>
                    <hr>
                    <p><strong>Login Sebagai:</strong> {{ Auth::user()->nama }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                    <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="returnBahanKadepModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnBahanKadepForm" action="" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-undo"></i> Kembalikan Sisa Bahan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Bahan:</strong> <span id="rbKadepName">-</span><br>
                        <strong>Jumlah Diambil:</strong> <span id="rbKadepAmbil">-</span>
                    </div>

                    <div class="mb-3">
                        <label for="rbKadepTerpakai" class="form-label">Jumlah Terpakai <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_terpakai" id="rbKadepTerpakai"
                               class="form-control" min="1" required>
                        <small class="text-muted">Berapa yang benar-benar terpakai</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sisa (Otomatis)</label>
                        <input type="text" id="rbKadepSisa" class="form-control" readonly value="0">
                    </div>

                    <div class="mb-3">
                        <label for="rbKadepJumlahKembali" class="form-label">Jumlah Dikembalikan <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_pengembalian" id="rbKadepJumlahKembali"
                               class="form-control" value="0" min="0" required>
                        <small class="text-muted">Berapa yang dikembalikan ke stok</small>
                    </div>

                    <div id="rbKadepError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="rbKadepSubmit">
                        <i class="fas fa-check"></i> Konfirmasi Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($peminjamPerBulan->count())
            const ctx = document.getElementById('peminjamChart').getContext('2d');
            const data = {
                labels: [
                    @foreach($peminjamPerBulan as $item)
                        '{{ \Carbon\Carbon::createFromDate(now()->year, $item->bulan, 1)->format('F') }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: [
                        @foreach($peminjamPerBulan as $item)
                            {{ $item->total }},
                        @endforeach
                    ],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.1
                }]
            };

            new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif

        const returnForm = document.getElementById('returnBahanKadepForm');
        if (returnForm) {
            const nameEl = document.getElementById('rbKadepName');
            const ambilEl = document.getElementById('rbKadepAmbil');
            const terpakaiEl = document.getElementById('rbKadepTerpakai');
            const sisaDisplayEl = document.getElementById('rbKadepSisa');
            const jumlahKembaliEl = document.getElementById('rbKadepJumlahKembali');
            const errorDiv = document.getElementById('rbKadepError');
            const submitBtn = document.getElementById('rbKadepSubmit');
            const modal = new bootstrap.Modal(document.getElementById('returnBahanKadepModal'));

            document.querySelectorAll('.btn-return-bahan-kadep').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const ambil = parseInt(this.dataset.ambil) || 0;
                    const satuan = this.dataset.satuan || '-';

                    returnForm.action = '/pemakaian_bahan/' + id + '/return';
                    nameEl.textContent = name;
                    ambilEl.textContent = ambil + ' ' + satuan;
                    terpakaiEl.value = '';
                    sisaDisplayEl.value = ambil;
                    jumlahKembaliEl.value = Math.max(0, ambil);
                    jumlahKembaliEl.max = ambil;

                    modal.show();
                    setTimeout(() => terpakaiEl.focus(), 300);
                });
            });

            terpakaiEl.addEventListener('input', function() {
                const ambil = parseInt(ambilEl.textContent?.split(' ')[0]) || 0;
                const pakai = parseInt(this.value) || 0;
                const sisa = Math.max(0, ambil - pakai);
                sisaDisplayEl.value = sisa;
                jumlahKembaliEl.value = Math.min(sisa, parseInt(jumlahKembaliEl.max) || sisa);
                jumlahKembaliEl.max = sisa;
            });

            returnForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                errorDiv.classList.add('d-none');

                fetch(returnForm.action, {
                    method: 'POST',
                    body: new FormData(returnForm),
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
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Konfirmasi Pengembalian';
                });
            });
        }
    });
</script>
@endpush

@include('dashboard._pinjam_pakai')

@endsection
