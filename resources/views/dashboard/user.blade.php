@extends('layouts.admin')

@section('title', 'Dashboard Mahasiswa')

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

    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3">Dashboard {{ ucfirst(Auth::user()->role) }}</h1>
            <p class="text-muted">Selamat datang, {{ Auth::user()->nama }}. Pilih laboratorium untuk meminjam alat atau bahan.</p>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small font-weight-bold">Sedang Dipinjam</div>
                    <div class="h3 mb-0">{{ $activeCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small font-weight-bold">Total Laboratorium</div>
                    <div class="h3 mb-0">{{ $labs->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small font-weight-bold">Riwayat Peminjaman</div>
                    <div class="h3 mb-0">{{ $riwayatCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small font-weight-bold">Total Alat Tersedia</div>
                    <div class="h3 mb-0">{{ $labs->sum('alat_count') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Laboratorium --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Daftar Laboratorium</h5>
            </div>
        </div>

        @forelse($labs as $lab)
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
                <div class="alert alert-info">
                    Belum ada laboratorium yang tersedia.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Peminjaman Aktif --}}
    @if($myPeminjaman->count())
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Peminjaman Aktif</h6>
                    <a href="{{ route('laporan.saya') }}" class="btn btn-sm btn-outline-primary">Lihat Semua →</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Alat/Unit</th>
                                    <th>Keperluan</th>
                                    <th>Dipinjam Sejak</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myPeminjaman as $peminjaman)
                                    <tr>
                                        <td>{{ $peminjaman->equipment_name }}</td>
                                        <td>{{ $peminjaman->keperluan }}</td>
                                        <td>{{ $peminjaman->waktu_peminjaman->format('d-m-Y H:i') }}</td>
                                        <td>
                                            @if($peminjaman->waktu_pengembalian)
                                                <small class="{{ $peminjaman->isOverdue() ? 'text-danger fw-bold' : 'text-muted' }}">
                                                    <i class="fas fa-{{ $peminjaman->isOverdue() ? 'exclamation-triangle' : 'clock' }}"></i>
                                                    {{ $peminjaman->waktu_pengembalian->format('d-m-Y H:i') }}
                                                    @if($peminjaman->isOverdue()) (overdue) @endif
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-warning">Terpinjam</span></td>
                                        <td>
                                            <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-sm btn-info">Detail</a>
                                            <button type="button" class="btn btn-sm btn-success btn-return"
                                                    data-id="{{ $peminjaman->id }}" data-name="{{ $peminjaman->equipment_name }}">
                                                <i class="fas fa-undo"></i> Kembalikan
                                            </button>
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

    {{-- Pemakaian Bahan Aktif --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Pemakaian Bahan</h5>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#pakaiBahanModal">
                    <i class="fas fa-flask me-1"></i> Pakai Bahan
                </button>
            </div>

            <div class="card shadow">
                <div class="card-body">
                    @if($myPemakaianBahan->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Bahan</th>
                                        <th>Dihat</th>
                                        <th>Diambil</th>
                                        <th>Terpakai</th>
                                        <th>Sisa</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myPemakaianBahan as $pemakaian)
@php
                                            $terpakai = $pemakaian->jumlah_terpakai ?? $pemakaian->jumlah_pengambilan;
                                            $sisa = $pemakaian->jumlah_pengambilan - $terpakai;
                                            $canReturn = $pemakaian->jumlah_pengembalian == null;
                                        @endphp>
                                            <td>{{ $pemakaian->bahan->nama_bahan ?? '-' }}</td>
                                            <td>{{ $pemakaian->pengadaanBahan->supplier ?? '-' }}</td>
                                            <td>{{ $pemakaian->jumlah_pengambilan }} {{ $pemakaian->bahan->satuan ?? '-' }}</td>
                                            <td>{{ $terpakai }} {{ $pemakaian->bahan->satuan ?? '-' }}</td>
                                            <td><span class="badge bg-info">{{ $sisa }} {{ $pemakaian->bahan->satuan ?? '-' }}</span></td>
                                            <td>
                                                @can('return', $pemakaian)
                                                    <button type="button" class="btn btn-sm btn-warning btn-return-bahan"
                                                            data-id="{{ $pemakaian->id }}"
                                                            data-name="{{ $pemakaian->bahan->nama_bahan ?? '-' }}"
                                                            data-ambil="{{ $pemakaian->jumlah_pengambilan }}"
                                                            data-satuan="{{ $pemakaian->bahan->satuan ?? '-' }}">
                                                        <i class="fas fa-undo"></i> Kembalikan Sisa
                                                    </button>
                                                @endcan
                                                @can('view', $pemakaian)
                                                    <a href="{{ route('pemakaian_bahan.show', $pemakaian) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i> Lihat
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-flask fa-2x mb-2"></i>
                            <p>Belum ada pemakaian bahan aktif.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Peminjaman Chart --}}
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-1"></i>Riwayat Peminjaman Saya ({{ date('Y') }})
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="riwayatPeminjamanChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    new Chart(document.getElementById('riwayatPeminjamanChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Peminjaman',
                data: @json($riwayatPeminjaman ?? []),
                borderColor: '#4e73df',
                tension: 0.3,
                fill: true,
                backgroundColor: 'rgba(78, 115, 223, 0.1)'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
    </script>

    <!-- Modal Pakai Bahan -->
    <div class="modal fade" id="pakaiBahanModal" tabindex="-1" aria-labelledby="pakaiBahanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="pakaiBahanForm" action="{{ route('pemakaian_bahan.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="pakaiBahanModalLabel">
                            <i class="fas fa-flask me-2"></i>Pakai Bahan
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="id_bahan" class="form-label">Pilih Bahan <span class="text-danger">*</span></label>
                            <select name="id_bahan" id="id_bahan" class="form-select" required>
                                <option value="">-- Pilih Bahan --</option>
                                @foreach($allBahans as $bahan)
                                    <option value="{{ $bahan['id'] }}" data-stok="{{ $bahan['stok'] }}" data-satuan="{{ $bahan['satuan'] }}"
                                            data-pengadaan='@json($bahan['pengadaan_options'])'>
                                        {{ $bahan['nama'] }} (Stok: {{ $bahan['stok'] }} {{ $bahan['satuan'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_pengadaan_bahan" class="form-label">Batch Pengadaan <span class="text-danger">*</span></label>
                            <select name="id_pengadaan_bahan" id="id_pengadaan_bahan" class="form-select" required>
                                <option value="">-- Pilih Bahan Dulu --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah_pengambilan" class="form-label">Jumlah Diambil <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_pengambilan" id="jumlah_pengambilan" class="form-control"
                                   min="1" required>
                            <small class="text-muted" id="stokInfo">Stok tersedia: -</small>
                        </div>
                        <div class="mb-3">
                            <label for="keperluan_pakai" class="form-label">Keperluan <span class="text-danger">*</span></label>
                            <input type="text" name="keperluan" id="keperluan_pakai" class="form-control"
                                   placeholder="Contoh: Praktikum TIG Welding" required>
                        </div>

                        <div id="pakaiBahanError" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" id="btnPakaiBahanSubmit">
                            <i class="fas fa-check"></i> Pakai Bahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Return Sisa Bahan -->
    <div class="modal fade" id="returnBahanDashModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="returnBahanDashForm" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-undo me-2"></i>Kembalikan Sisa Bahan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <strong>Bahan:</strong> <span id="returnBahanName">-</span><br>
                            <strong>Jumlah Diambil:</strong> <span id="returnBahanAmbil">-</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Terpakai <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_terpakai" id="returnTerpakai" class="form-control" min="1" required>
                            <small class="text-muted">Berapa yang benar-benar terpakai</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sisa (Otomatis)</label>
                            <input type="text" id="returnSisaDisplay" class="form-control" readonly value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Dikembalikan <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_pengembalian" id="returnJumlahKembali" class="form-control" min="0" value="0" required>
                            <small class="text-muted">Sisa yang dikembalikan ke stok</small>
                        </div>
                        <div id="returnBahanDashError" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning" id="btnReturnBahanDashSubmit">
                            <i class="fas fa-check"></i> Konfirmasi Pengembalian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Quick Return -->
    <div class="modal fade" id="modalReturn" tabindex="-1" aria-labelledby="modalReturnLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formQuickReturn" method="POST">
                    @csrf
                    <input type="hidden" name="waktu_kembali_aktual" id="returnTime">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalReturnLabel">
                            <i class="fas fa-undo me-2"></i>Kembalikan Alat
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alat</label>
                            <p class="form-control-plaintext" id="returnAlatName">-</p>
                        </div>
                        <div class="mb-3">
                            <label for="returnKondisi" class="form-label">Kondisi Saat Dikembalikan <span class="text-danger">*</span></label>
                            <select name="kondisi_saat_pengembalian" id="returnKondisi" class="form-select" required>
                                <option value="">Pilih Kondisi</option>
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Kembalikan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalReturn'));
        const form = document.getElementById('formQuickReturn');
        const inputTime = document.getElementById('returnTime');
        const inputName = document.getElementById('returnAlatName');
        const inputKondisi = document.getElementById('returnKondisi');

        document.querySelectorAll('.btn-return').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                form.action = '/peminjaman/' + id + '/return';
                inputTime.value = new Date().toISOString().slice(0, 16);
                inputName.textContent = name;
                inputKondisi.value = '';

                modal.show();
                setTimeout(() => inputKondisi.focus(), 300);
            });
});
    });
    </script>

    <script>
    // Handle Bahan selection to show batches
    document.getElementById('id_bahan')?.addEventListener('change', function() {
        const bahanId = this.value;
        const batchSelect = document.getElementById('id_pengadaan_bahan');
        const stokInfo = document.getElementById('stokInfo');
        const dataOption = this.options[this.selectedIndex]?.dataset;
        
        // Reset batch select
        batchSelect.innerHTML = '<option value="">-- Pilih Bahan Dulu --</option>';
        
        if (!bahanId) {
            stokInfo.textContent = 'Stok tersedia: -';
            return;
        }
        
        const selectedOption = this.options[this.selectedIndex];
        if (!selectedOption || !dataOption) return;
        
        const stok = selectedOption.getAttribute('data-stok');
        const satuan = selectedOption.getAttribute('data-satuan');
        const pengadaanOptions = JSON.parse(dataOption.getAttribute('data-pengadaan') || '[]');
        
        stokInfo.textContent = `Stok tersedia: ${stok} ${satuan}`;
        
        // Populate batch options
        if (pengadaanOptions.length > 0) {
            pengadaanOptions.forEach(function(opt) {
                const optEl = document.createElement('option');
                optEl.value = opt.id;
                optEl.textContent = opt.label;
                batchSelect.appendChild(optEl);
            });
        }
    });

    // Format sisa when input jumlah terpakai changes
    document.getElementById('jumlah_pengambilan')?.parentElement?.addEventListener('input', function() {
        const ambil = parseInt(document.getElementById('jumlah_pengambilan')?.value) || 0;
        // Note: This is a simplified check - in real app would need the actualambil value from hidden
    });
    </script>

    <script>
    // Return Sisa Bahan Modal (new)
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('returnBahanDashModal'));
        const form = document.getElementById('returnBahanDashForm');
        const nameEl = document.getElementById('returnBahanName');
        const ambilEl = document.getElementById('returnBahanAmbil');
        const terpakaiEl = document.getElementById('returnTerpakai');
        const sisaDisplayEl = document.getElementById('returnSisaDisplay');
        const jumlahKembaliEl = document.getElementById('returnJumlahKembali');
        const errorDiv = document.getElementById('returnBahanDashError');
        const submitBtn = document.getElementById('btnReturnBahanDashSubmit');

        // Open modal when clicking "Kembalikan Sisa" button
        document.querySelectorAll('.btn-return-bahan').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const ambil = parseInt(this.dataset.ambil) || 0;
                const satuan = this.dataset.satuan || '-';

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

        // Format sisa when input changes
        terpakaiEl.addEventListener('input', function() {
            const ambil = parseInt(ambilEl.textContent?.split(' ')[0]) || 0;
            const pakai = parseInt(this.value) || 0;
            const sisa = Math.max(0, ambil - pakai);
            sisaDisplayEl.value = sisa;
            jumlahKembaliEl.value = Math.min(sisa, parseInt(jumlahKembaliEl.max) || sisa);
            jumlahKembaliEl.max = sisa;
        });

        // Handle form submission via AJAX
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
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
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Konfirmasi Pengembalian';
            });
        });
    });
    </script>

    @push('css')
<style>
    .lab-card {
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .lab-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.2) !important;
    }
    .lab-card .card-img-top {
        transition: transform 0.3s ease;
    }
    .lab-card:hover .card-img-top {
        transform: scale(1.05);
    }
</style>
@endpush
</div>
@endsection
