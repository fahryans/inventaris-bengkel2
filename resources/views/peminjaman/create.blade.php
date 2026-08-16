@extends('layouts.admin')

@section('title', 'Tambah Peminjaman')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}">Data Peminjaman</a></li>
            <li class="breadcrumb-item active">Tambah Peminjaman</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Tambah Peminjaman</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('peminjaman.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="tipe_pelacakan" class="form-label">Tipe Peminjaman <span class="text-danger">*</span></label>
                    <select name="tipe_pelacakan" id="tipe_pelacakan" class="form-select" required>
                        <option value="">Pilih Tipe</option>
                        <option value="agregat" {{ old('tipe_pelacakan') == 'agregat' ? 'selected' : '' }}>Alat (Agregat)</option>
                        <option value="unit" {{ old('tipe_pelacakan') == 'unit' ? 'selected' : '' }}>Unit Alat (Individual)</option>
                    </select>
                </div>

                <div id="field-alat" class="mb-3" style="display: none;">
                    <label for="id_alat" class="form-label">Pilih Alat <span class="text-danger">*</span></label>
                    <select name="id_alat" id="id_alat" class="form-select @error('id_alat') is-invalid @enderror">
                        <option value="">Pilih Alat</option>
                        @foreach($alats as $alat)
                            <option value="{{ $alat->id }}" {{ old('id_alat') == $alat->id ? 'selected' : '' }}>
                                {{ $alat->nama_alat }} ({{ $alat->getAvailableQuantity() }} tersedia)
                            </option>
                        @endforeach
                    </select>
                    @error('id_alat')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div id="field-unit" class="mb-3" style="display: none;">
                    <label for="id_unit_alat" class="form-label">Pilih Unit Alat <span class="text-danger">*</span></label>
                    <select name="id_unit_alat" id="id_unit_alat" class="form-select @error('id_unit_alat') is-invalid @enderror">
                        <option value="">Pilih Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('id_unit_alat') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->kode_inventaris }} - {{ $unit->alat->nama_alat }} [{{ ucfirst($unit->status) }}]
                            </option>
                        @endforeach
                    </select>
                    @error('id_unit_alat')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="keperluan" class="form-label">Keperluan <span class="text-danger">*</span></label>
                    <input type="text" name="keperluan" id="keperluan" class="form-control @error('keperluan') is-invalid @enderror" 
                           value="{{ old('keperluan') }}" required>
                    @error('keperluan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="waktu_peminjaman" class="form-label">Waktu Peminjaman <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="waktu_peminjaman" id="waktu_peminjaman" class="form-control @error('waktu_peminjaman') is-invalid @enderror" 
                                   value="{{ old('waktu_peminjaman') }}" required>
                            @error('waktu_peminjaman')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="waktu_pengembalian" class="form-label">Waktu Pengembalian <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="waktu_pengembalian" id="waktu_pengembalian" class="form-control @error('waktu_pengembalian') is-invalid @enderror" 
                                   value="{{ old('waktu_pengembalian') }}" required>
                            @error('waktu_pengembalian')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="kondisi_saat_peminjaman" class="form-label">Kondisi Saat Peminjaman <span class="text-danger">*</span></label>
                    <select name="kondisi_saat_peminjaman" id="kondisi_saat_peminjaman" class="form-select @error('kondisi_saat_peminjaman') is-invalid @enderror" required>
                        <option value="">Pilih Kondisi</option>
                        <option value="baik" {{ old('kondisi_saat_peminjaman') == 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak_ringan" {{ old('kondisi_saat_peminjaman') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ old('kondisi_saat_peminjaman') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                    @error('kondisi_saat_peminjaman')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">Batal</a>
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
    document.addEventListener('DOMContentLoaded', function() {
        const tipeSelect = document.getElementById('tipe_pelacakan');
        const fieldAlat = document.getElementById('field-alat');
        const fieldUnit = document.getElementById('field-unit');

        function toggleFields() {
            const tipe = tipeSelect.value;
            fieldAlat.style.display = (tipe === 'agregat') ? 'block' : 'none';
            fieldUnit.style.display = (tipe === 'unit') ? 'block' : 'none';
        }

        tipeSelect.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>
@endpush
@endsection
