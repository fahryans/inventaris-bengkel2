@extends('layouts.admin')

@section('title', 'Tambah Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bahan.index') }}">Data Bahan</a></li>
            <li class="breadcrumb-item active">Tambah Bahan</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9]">
            <h5 class="mb-0">Form Tambah Bahan</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('bahan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori" id="id_kategori" class="form-select @error('id_kategori') is-invalid @enderror" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" {{ old('id_kategori') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kategori')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_labor" class="form-label">Laboratorium <span class="text-danger">*</span></label>
                            <select name="id_labor" id="id_labor" class="form-select @error('id_labor') is-invalid @enderror" required>
                                <option value="">Pilih Lab</option>
                                @foreach($laboratoriums as $lab)
                                    <option value="{{ $lab->id }}" {{ old('id_labor') == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->nama_labor }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_labor')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nama_bahan" class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_bahan" id="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror" 
                           value="{{ old('nama_bahan') }}" required>
                    @error('nama_bahan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="stok_saat_ini" class="form-label">Stok Saat Ini <span class="text-danger">*</span></label>
                            <input type="number" name="stok_saat_ini" id="stok_saat_ini" class="form-control @error('stok_saat_ini') is-invalid @enderror" 
                                   value="{{ old('stok_saat_ini') }}" min="0" required>
                            @error('stok_saat_ini')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="stok_minimum" class="form-label">Stok Minimum <span class="text-danger">*</span></label>
                            <input type="number" name="stok_minimum" id="stok_minimum" class="form-control @error('stok_minimum') is-invalid @enderror" 
                                   value="{{ old('stok_minimum') }}" min="0" required>
                            @error('stok_minimum')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" id="satuan" class="form-control @error('satuan') is-invalid @enderror" 
                                   value="{{ old('satuan') }}" placeholder="ml, gram, pcs, dll" required>
                            @error('satuan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="merek" class="form-label">Merek</label>
                            <input type="text" name="merek" id="merek" class="form-control @error('merek') is-invalid @enderror" 
                                   value="{{ old('merek') }}">
                            @error('merek')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="spesifikasi" class="form-label">Spesifikasi</label>
                            <input type="text" name="spesifikasi" id="spesifikasi" class="form-control @error('spesifikasi') is-invalid @enderror" 
                                   value="{{ old('spesifikasi') }}">
                            @error('spesifikasi')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="foto" class="form-label">Foto</label>
                    <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                    @error('foto')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('bahan.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
