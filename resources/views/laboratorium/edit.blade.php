@extends('layouts.admin')

@section('title', 'Edit Laboratorium')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('laboratorium.index') }}">Data Laboratorium</a></li>
            <li class="breadcrumb-item active">Edit Lab</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Edit Laboratorium</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('laboratorium.update', $laboratorium) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="id_user_kalab" class="form-label">Kepala Laboratorium <span class="text-danger">*</span></label>
                    <select name="id_user_kalab" id="id_user_kalab" class="form-select @error('id_user_kalab') is-invalid @enderror" required>
                        <option value="">Pilih Kepala Lab</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('id_user_kalab', $laboratorium->id_user_kalab) == $user->id ? 'selected' : '' }}>
                                {{ $user->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_user_kalab')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nama_labor" class="form-label">Nama Laboratorium <span class="text-danger">*</span></label>
                    <input type="text" name="nama_labor" id="nama_labor" class="form-control @error('nama_labor') is-invalid @enderror" 
                           value="{{ old('nama_labor', $laboratorium->nama_labor) }}" required>
                    @error('nama_labor')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                    <input type="text" name="lokasi" id="lokasi" class="form-control @error('lokasi') is-invalid @enderror" 
                           value="{{ old('lokasi', $laboratorium->lokasi) }}" required>
                    @error('lokasi')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="sop" class="form-label">SOP (Standar Operasional Prosedur)</label>
                    <textarea name="sop" id="sop" class="form-control @error('sop') is-invalid @enderror" 
                              rows="5">{{ old('sop', $laboratorium->sop) }}</textarea>
                    @error('sop')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('laboratorium.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
