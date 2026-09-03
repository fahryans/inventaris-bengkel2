@extends('layouts.admin')

@section('title', 'Edit Pengadaan Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pengadaan_bahan.index') }}">Data Pengadaan Bahan</a></li>
            <li class="breadcrumb-item active">Edit Pengadaan</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Edit Pengadaan Bahan</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('pengadaan_bahan.update', $pengadaan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_bahan" class="form-label">Bahan <span class="text-danger">*</span></label>
                            <select name="id_bahan" id="id_bahan" class="form-select @error('id_bahan') is-invalid @enderror" required>
                                <option value="">Pilih Bahan</option>
                                @foreach($bahans as $bahan)
                                    <option value="{{ $bahan->id }}" {{ old('id_bahan', $pengadaan->id_bahan) == $bahan->id ? 'selected' : '' }}
                                            data-spesifikasi='{!! json_encode($bahan->spesifikasiBahan->map(fn($s) => ["id" => $s->id, "kode" => $s->kode_spesifikasi, "nama" => $s->nama_spesifikasi])) !!}'>
                                        {{ $bahan->nama_bahan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_bahan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_spesifikasi_bahan" class="form-label">Spesifikasi <span class="text-danger">*</span></label>
                            <select name="id_spesifikasi_bahan" id="id_spesifikasi_bahan" class="form-select @error('id_spesifikasi_bahan') is-invalid @enderror" required>
                                <option value="">Pilih Spesifikasi</option>
                            </select>
                            @error('id_spesifikasi_bahan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_pengadaan" class="form-label">Tanggal Pengadaan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengadaan" id="tanggal_pengadaan" class="form-control @error('tanggal_pengadaan') is-invalid @enderror" 
                                   value="{{ old('tanggal_pengadaan', $pengadaan->tanggal_pengadaan?->format('Y-m-d')) }}" required>
                            @error('tanggal_pengadaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control @error('tanggal_masuk') is-invalid @enderror" 
                                   value="{{ old('tanggal_masuk', $pengadaan->tanggal_masuk?->format('Y-m-d')) }}">
                            @error('tanggal_masuk')
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
                                   value="{{ old('harga_perolehan', $pengadaan->harga_perolehan) }}" min="0" step="0.01" required>
                            @error('harga_perolehan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" id="jumlah" class="form-control @error('jumlah') is-invalid @enderror" 
                                   value="{{ old('jumlah', $pengadaan->jumlah) }}" min="1" required>
                            @error('jumlah')
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
                                   value="{{ old('merek', $pengadaan->merek) }}" required>
                            @error('merek')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="masa_expire_bahan" class="form-label">Masa Expire Bahan</label>
                            <input type="date" name="masa_expire_bahan" id="masa_expire_bahan" class="form-control @error('masa_expire_bahan') is-invalid @enderror" 
                                   value="{{ old('masa_expire_bahan', $pengadaan->masa_expire_bahan?->format('Y-m-d')) }}">
                            @error('masa_expire_bahan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="supplier" class="form-label">Supplier <span class="text-danger">*</span></label>
                    <input type="text" name="supplier" id="supplier" class="form-control @error('supplier') is-invalid @enderror" 
                           value="{{ old('supplier', $pengadaan->supplier) }}" required>
                    @error('supplier')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="foto_transaksi" class="form-label">Foto Transaksi</label>
                    @if($pengadaan->foto_transaksi)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $pengadaan->foto_transaksi) }}" alt="Foto Transaksi" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    @endif
                    <input type="file" name="foto_transaksi" id="foto_transaksi" class="form-control @error('foto_transaksi') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, GIF, WEBP, SVG, dll (Max 5MB)</small>
                    @error('foto_transaksi')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('pengadaan_bahan.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
document.getElementById('id_bahan').addEventListener('change', function() {
    populateSpesifikasi(this);
});

function populateSpesifikasi(selectElement) {
    const selected = selectElement.options[selectElement.selectedIndex];
    const spesifikasiSelect = document.getElementById('id_spesifikasi_bahan');
    const currentValue = spesifikasiSelect.dataset.currentValue;

    // Clear spesifikasi options
    spesifikasiSelect.innerHTML = '<option value="">Pilih Spesifikasi</option>';

    const spesifikasiData = selected.getAttribute('data-spesifikasi');
    let hasSelected = false;

    if (spesifikasiData) {
        const spesifikasis = JSON.parse(spesifikasiData.replace(/&quot;/g, '"'));
        if (spesifikasis.length > 0) {
            spesifikasis.forEach(function(spec) {
                const option = document.createElement('option');
                option.value = spec.id;
                option.textContent = spec.kode + ' - ' + spec.nama;
                spesifikasiSelect.appendChild(option);
                // Keep existing selection
                if (String(spec.id) === String(currentValue)) {
                    option.selected = true;
                    hasSelected = true;
                }
            });
        } else {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Tidak ada spesifikasi untuk bahan ini';
            spesifikasiSelect.appendChild(option);
        }
    }

    // If nothing selected and dataset currentValue set, try to keep it
    if (!hasSelected && currentValue) {
        spesifikasiSelect.value = currentValue;
    }
}

// On load: populate spesifikasi based on current selected bahan
(function() {
    const bahanSelect = document.getElementById('id_bahan');
    const spesifikasiSelect = document.getElementById('id_spesifikasi_bahan');
    // Store current pengadaan's id_spesifikasi
    spesifikasiSelect.dataset.currentValue = '{{ old("id_spesifikasi_bahan", $pengadaan->id_spesifikasi_bahan) }}';
    populateSpesifikasi(bahanSelect);
})();
</script>
@endpush
@endsection
