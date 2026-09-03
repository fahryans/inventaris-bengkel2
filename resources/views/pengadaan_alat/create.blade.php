@extends('layouts.admin')

@section('title', 'Tambah Pengadaan Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pengadaan_alat.index') }}">Data Pengadaan Alat</a></li>
            <li class="breadcrumb-item active">Tambah Pengadaan</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Tambah Pengadaan Alat</h5>
        </div>

        <div class="card-body">
            <div id="info-unit" class="alert alert-info d-none">
                <i class="fas fa-info-circle"></i>
                <strong>Tipe Unit:</strong> Kode inventaris akan diinput saat pengeditan unit alat.
            </div>

            <form action="{{ route('pengadaan_alat.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_alat" class="form-label">Alat <span class="text-danger">*</span></label>
                            <select name="id_alat" id="id_alat" class="form-select @error('id_alat') is-invalid @enderror" required>
                                <option value="">Pilih Alat</option>
                                @foreach($alats as $alat)
                                    <option value="{{ $alat->id }}" {{ old('id_alat') == $alat->id ? 'selected' : '' }}
                                            data-tipe="{{ $alat->tipe_pelacakan }}"
                                            data-spesifikasi='@json($alat->spesifikasiAlat->map(fn($s) => ['id' => $s->id, 'kode' => $s->kode_spesifikasi, 'nama' => $s->nama_spesifikasi]))'>
                                        {{ $alat->nama_alat }} ({{ ucfirst($alat->tipe_pelacakan) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_alat')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_spesifikasi_alat" class="form-label">Spesifikasi <span class="text-danger">*</span></label>
                            <select name="id_spesifikasi_alat" id="id_spesifikasi_alat" class="form-select @error('id_spesifikasi_alat') is-invalid @enderror" required>
                                <option value="">Pilih Spesifikasi</option>
                            </select>
                            @error('id_spesifikasi_alat')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div id="kode-inventaris-wrapper" class="mb-3">
                    <label for="kode_inventaris" class="form-label">Kode Inventaris <span class="text-danger">*</span></label>
                    <input type="text" name="kode_inventaris" id="kode_inventaris" class="form-control @error('kode_inventaris') is-invalid @enderror" 
                           value="{{ old('kode_inventaris') }}" placeholder="Contoh: TP-INV-001" required>
                    <small class="text-muted">Kode unik untuk batch pengadaan ini (manual input)</small>
                    @error('kode_inventaris')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_pengadaan" class="form-label">Tanggal Pengadaan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengadaan" id="tanggal_pengadaan" class="form-control @error('tanggal_pengadaan') is-invalid @enderror" 
                                   value="{{ old('tanggal_pengadaan', date('Y-m-d')) }}" required>
                            @error('tanggal_pengadaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control @error('tanggal_masuk') is-invalid @enderror" 
                                   value="{{ old('tanggal_masuk') }}">
                            @error('tanggal_masuk')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="merek" class="form-label">Merek <span class="text-danger">*</span></label>
                            <input type="text" name="merek" id="merek" class="form-control @error('merek') is-invalid @enderror" 
                                   value="{{ old('merek') }}" required>
                            @error('merek')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" id="jumlah" class="form-control @error('jumlah') is-invalid @enderror" 
                                   value="{{ old('jumlah') }}" min="1" required>
                            <small id="jumlah-help" class="text-muted d-none">Jumlah unit yang akan dibuat</small>
                            @error('jumlah')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="harga_perolehan" class="form-label">Harga Perolehan <span class="text-danger">*</span></label>
                            <input type="number" name="harga_perolehan" id="harga_perolehan" class="form-control @error('harga_perolehan') is-invalid @enderror" 
                                   value="{{ old('harga_perolehan') }}" min="0" step="0.01" required>
                            @error('harga_perolehan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="supplier" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <input type="text" name="supplier" id="supplier" class="form-control @error('supplier') is-invalid @enderror" 
                                   value="{{ old('supplier') }}" required>
                            @error('supplier')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="foto_transaksi" class="form-label">Foto Transaksi</label>
                    <input type="file" name="foto_transaksi" id="foto_transaksi" class="form-control @error('foto_transaksi') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, GIF, WEBP, SVG, dll (Max 5MB)</small>
                    @error('foto_transaksi')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('pengadaan_alat.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.getElementById('id_alat').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const spesifikasiSelect = document.getElementById('id_spesifikasi_alat');
    const kodeInput = document.getElementById('kode_inventaris');
    const kodeWrapper = document.getElementById('kode-inventaris-wrapper');
    const infoUnit = document.getElementById('info-unit');
    const jumlahHelp = document.getElementById('jumlah-help');
    
    // Clear spesifikasi options
    spesifikasiSelect.innerHTML = '<option value="">Pilih Spesifikasi</option>';
    
    // Get tipe pelacakan
    const tipe = selected.getAttribute('data-tipe');
    
    // Show/hide kode inventaris berdasarkan tipe
    if (tipe === 'unit') {
        kodeWrapper.classList.add('d-none');
        kodeInput.removeAttribute('required');
        kodeInput.value = '';
        infoUnit.classList.remove('d-none');
        jumlahHelp.classList.remove('d-none');
    } else {
        kodeWrapper.classList.remove('d-none');
        kodeInput.setAttribute('required', 'required');
        infoUnit.classList.add('d-none');
        jumlahHelp.classList.add('d-none');
    }
    
    // Get spesifikasi data
    const spesifikasiData = selected.getAttribute('data-spesifikasi');
    
    if (spesifikasiData) {
        const spesifikasis = JSON.parse(spesifikasiData);
        spesifikasis.forEach(function(spec) {
            const option = document.createElement('option');
            option.value = spec.id;
            option.textContent = spec.kode + ' - ' + spec.nama;
            spesifikasiSelect.appendChild(option);
        });
    }
});
</script>
@endpush
