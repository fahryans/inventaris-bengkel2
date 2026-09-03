@extends('layouts.admin')

@section('title', 'Edit Pemeliharaan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pemeliharaan.index') }}">Data Pemeliharaan</a></li>
            <li class="breadcrumb-item active">Edit Pemeliharaan</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Edit Pemeliharaan</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('pemeliharaan.update', $pemeliharaan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="id_unit_alat" class="form-label">Unit Alat <span class="text-danger">*</span></label>
                    <select name="id_unit_alat" id="id_unit_alat" class="form-select @error('id_unit_alat') is-invalid @enderror" required>
                        <option value="">Pilih Unit Alat</option>
                        @foreach($unitAlats as $unit)
                            <option value="{{ $unit->id }}" {{ old('id_unit_alat', $pemeliharaan->id_unit_alat) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->alat->nama_alat }} ({{ $unit->kode_inventaris ?? '#' . $unit->id }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_unit_alat')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_teknisi" class="form-label">Teknisi <span class="text-danger">*</span></label>
                    <select name="id_teknisi" id="id_teknisi" class="form-select @error('id_teknisi') is-invalid @enderror" required>
                        <option value="">Pilih Teknisi</option>
                        @foreach($teknisis as $tek)
                            <option value="{{ $tek->id }}" {{ old('id_teknisi', $pemeliharaan->id_teknisi) == $tek->id ? 'selected' : '' }}>
                                {{ $tek->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_teknisi')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_cek" class="form-label">Tanggal Cek <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_cek" id="tanggal_cek" class="form-control @error('tanggal_cek') is-invalid @enderror" 
                                   value="{{ old('tanggal_cek', $pemeliharaan->tanggal_cek?->format('Y-m-d')) }}" required>
                            @error('tanggal_cek')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_cek_berikutnya" class="form-label">Tanggal Cek Berikutnya <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_cek_berikutnya" id="tanggal_cek_berikutnya" class="form-control @error('tanggal_cek_berikutnya') is-invalid @enderror" 
                                   value="{{ old('tanggal_cek_berikutnya', $pemeliharaan->tanggal_cek_berikutnya->format('Y-m-d')) }}" required>
                            @error('tanggal_cek_berikutnya')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kondisi" class="form-label">Kondisi <span class="text-danger">*</span></label>
                            <select name="kondisi" id="kondisi" class="form-select @error('kondisi') is-invalid @enderror" required>
                                <option value="">Pilih Kondisi</option>
                                <option value="baik" {{ old('kondisi', $pemeliharaan->kondisi) == 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak_ringan" {{ old('kondisi', $pemeliharaan->kondisi) == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="rusak_berat" {{ old('kondisi', $pemeliharaan->kondisi) == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                            @error('kondisi')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="biaya" class="form-label">Biaya</label>
                            <input type="number" name="biaya" id="biaya" class="form-control @error('biaya') is-invalid @enderror" 
                                   value="{{ old('biaya', $pemeliharaan->biaya) }}" min="0" step="0.01">
                            @error('biaya')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="detail_biaya" class="form-label">Detail Biaya</label>
                    <textarea name="detail_biaya" id="detail_biaya" class="form-control @error('detail_biaya') is-invalid @enderror" 
                              rows="3">{{ old('detail_biaya', $pemeliharaan->detail_biaya) }}</textarea>
                    @error('detail_biaya')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="catatan" class="form-label">Catatan</label>
                    <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" 
                              rows="3">{{ old('catatan', $pemeliharaan->catatan) }}</textarea>
                    @error('catatan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="hasil_pemeliharaan" class="form-label">Hasil Pemeliharaan</label>
                    <textarea name="hasil_pemeliharaan" id="hasil_pemeliharaan" class="form-control @error('hasil_pemeliharaan') is-invalid @enderror" 
                              rows="3">{{ old('hasil_pemeliharaan', $pemeliharaan->hasil_pemeliharaan) }}</textarea>
                    @error('hasil_pemeliharaan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('pemeliharaan.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
