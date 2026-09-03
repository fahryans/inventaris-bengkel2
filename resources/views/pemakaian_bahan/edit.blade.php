@extends('layouts.admin')

@section('title', 'Edit Pemakaian Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pemakaian_bahan.index') }}">Data Pemakaian Bahan</a></li>
            <li class="breadcrumb-item active">Edit Pemakaian</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9]">
            <h5 class="mb-0">Form Edit Pemakaian Bahan</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('pemakaian_bahan.update', $pemakaian) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="id_bahan" class="form-label">Bahan <span class="text-danger">*</span></label>
                    <select name="id_bahan" id="id_bahan" class="form-select @error('id_bahan') is-invalid @enderror" required>
                        <option value="">Pilih Bahan</option>
                        @foreach($bahans as $bahan)
                            <option value="{{ $bahan->id }}" {{ old('id_bahan', $pemakaian->id_bahan) == $bahan->id ? 'selected' : '' }}>
                                {{ $bahan->nama_bahan }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_bahan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i>
                    Batch pengadaan dipilih otomatis oleh sistem (FIFO: batch paling lama yang masih tersedia dahulu).
                </div>

                <div class="mb-3">
                    <label for="keperluan" class="form-label">Keperluan <span class="text-danger">*</span></label>
                    <input type="text" name="keperluan" id="keperluan" class="form-control @error('keperluan') is-invalid @enderror" 
                           value="{{ old('keperluan', $pemakaian->keperluan) }}" required>
                    @error('keperluan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="jumlah_pengambilan" class="form-label">Jumlah Pengambilan <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_pengambilan" id="jumlah_pengambilan" class="form-control @error('jumlah_pengambilan') is-invalid @enderror" 
                                   value="{{ old('jumlah_pengambilan', $pemakaian->jumlah_pengambilan) }}" min="0" required>
                            @error('jumlah_pengambilan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="jumlah_terpakai" class="form-label">Jumlah Terpakai <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_terpakai" id="jumlah_terpakai" class="form-control @error('jumlah_terpakai') is-invalid @enderror" 
                                   value="{{ old('jumlah_terpakai', $pemakaian->jumlah_terpakai) }}" min="0" required>
                            @error('jumlah_terpakai')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="jumlah_pengembalian" class="form-label">Jumlah Pengembalian <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_pengembalian" id="jumlah_pengembalian" class="form-control @error('jumlah_pengembalian') is-invalid @enderror" 
                                   value="{{ old('jumlah_pengembalian', $pemakaian->jumlah_pengembalian) }}" min="0" required>
                            @error('jumlah_pengembalian')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="waktu_pemakaian" class="form-label">Waktu Pemakaian <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="waktu_pemakaian" id="waktu_pemakaian" class="form-control @error('waktu_pemakaian') is-invalid @enderror" 
                           value="{{ old('waktu_pemakaian', $pemakaian->waktu_pemakaian?->format('Y-m-d\TH:i')) }}" required>
                    @error('waktu_pemakaian')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('pemakaian_bahan.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
