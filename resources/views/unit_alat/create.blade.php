@extends('layouts.admin')

@section('title', 'Tambah Unit Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('unit-alat.index') }}">Data Unit Alat</a></li>
            <li class="breadcrumb-item active">Tambah Unit Alat</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Tambah Unit Alat</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('unit-alat.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_alat" class="form-label">Alat <span class="text-danger">*</span></label>
                            <select name="id_alat" id="id_alat" class="form-select @error('id_alat') is-invalid @enderror" required>
                                <option value="">Pilih Alat</option>
                                @foreach($alats as $alat)
                                    <option value="{{ $alat->id }}" {{ old('id_alat') == $alat->id ? 'selected' : '' }}
                                            data-spesifikasi='@json($alat->spesifikasiAlat->map(fn($s) => ['id' => $s->id, 'kode' => $s->kode_spesifikasi, 'nama' => $s->nama_spesifikasi]))'>
                                        {{ $alat->nama_alat }}
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kode_inventaris" class="form-label">Kode Inventaris <span class="text-danger">*</span></label>
                            <input type="text" name="kode_inventaris" id="kode_inventaris" class="form-control @error('kode_inventaris') is-invalid @enderror" 
                                   value="{{ old('kode_inventaris') }}" placeholder="Contoh: CNC-001" required>
                            <small class="text-muted">Kode unik untuk unit ini (manual input)</small>
                            @error('kode_inventaris')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kondisi_saat_ini" class="form-label">Kondisi Saat Ini <span class="text-danger">*</span></label>
                            <select name="kondisi_saat_ini" id="kondisi_saat_ini" class="form-select @error('kondisi_saat_ini') is-invalid @enderror" required>
                                <option value="">Pilih Kondisi</option>
                                <option value="baik" {{ old('kondisi_saat_ini') == 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak_ringan" {{ old('kondisi_saat_ini') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="rusak_berat" {{ old('kondisi_saat_ini') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                            @error('kondisi_saat_ini')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">Pilih Status</option>
                                <option value="tersedia" {{ old('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="dipinjam" {{ old('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('unit-alat.index') }}" class="btn btn-secondary">Batal</a>
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
    
    // Clear spesifikasi options
    spesifikasiSelect.innerHTML = '<option value="">Pilih Spesifikasi</option>';
    
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
