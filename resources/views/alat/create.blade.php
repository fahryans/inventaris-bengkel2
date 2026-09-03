@extends('layouts.admin')

@section('title', 'Tambah Alat')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('alat.index') }}">Data Alat</a></li>
            <li class="breadcrumb-item active">Tambah Alat</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9]">
            <h5 class="mb-0">Form Tambah Alat</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('alat.store') }}" method="POST" enctype="multipart/form-data">
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
                            @if($isTeknisi)
                                <label class="form-label">Laboratorium</label>
                                <input type="hidden" name="id_labor" value="{{ $autoLab?->id }}">
                                <input type="text" class="form-control" value="{{ $autoLab?->nama_labor ?? '-' }}" readonly>
                            @else
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
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nama_alat" class="form-label">Nama Alat <span class="text-danger">*</span></label>
                    <input type="text" name="nama_alat" id="nama_alat" class="form-control @error('nama_alat') is-invalid @enderror" 
                           value="{{ old('nama_alat') }}" required>
                    @error('nama_alat')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tipe_pelacakan" class="form-label">Tipe Pelacakan <span class="text-danger">*</span></label>
                            <select name="tipe_pelacakan" id="tipe_pelacakan" class="form-select @error('tipe_pelacakan') is-invalid @enderror" required>
                                <option value="">Pilih Tipe</option>
                                <option value="agregat" {{ old('tipe_pelacakan') == 'agregat' ? 'selected' : '' }}>Agregat</option>
                                <option value="unit" {{ old('tipe_pelacakan') == 'unit' ? 'selected' : '' }}>Unit</option>
                            </select>
                            @error('tipe_pelacakan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto</label>
                            <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF, WEBP, SVG, dll (Max 5MB)</small>
                            @error('foto')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Spesifikasi Alat</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddSpesifikasi">
                                <i class="fas fa-plus"></i> Tambah Spesifikasi
                            </button>
                        </div>
                        <p class="text-muted small">Tambahkan minimal satu spesifikasi agar saat pengadaan alat hanya perlu memilih spesifikasi.</p>

                        <div id="spesifikasiContainer"></div>

                        @error('spesifikasi')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('alat.index') }}" class="btn btn-secondary">Batal</a>
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
(function () {
    const container = document.getElementById('spesifikasiContainer');
    let index = container.querySelectorAll('.spesifikasi-row').length;

    function rowHtml(i) {
        return '<div class="row g-3 mb-3 spesifikasi-row align-items-center">'
            + '<div class="col-md-3">'
            + '<input type="text" name="spesifikasi[' + i + '][kode_spesifikasi]" class="form-control" placeholder="Kode Spesifikasi (mis: PK-01)" required>'
            + '</div>'
            + '<div class="col-md-3">'
            + '<input type="text" name="spesifikasi[' + i + '][nama_spesifikasi]" class="form-control" placeholder="Nama Spesifikasi (mis: Premium)" required>'
            + '</div>'
            + '<div class="col-md-5">'
            + '<input type="text" name="spesifikasi[' + i + '][deskripsi]" class="form-control" placeholder="Deskripsi (opsional)">'
            + '</div>'
            + '<div class="col-md-1 text-end">'
            + '<button type="button" class="btn btn-outline-danger btn-sm btn-remove-spesifikasi" title="Hapus"><i class="fas fa-trash"></i></button>'
            + '</div>'
            + '</div>';
    }

    function addRow() {
        container.insertAdjacentHTML('beforeend', rowHtml(index));
        index++;
    }

    document.getElementById('btnAddSpesifikasi').addEventListener('click', addRow);

    container.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-spesifikasi')) {
            e.target.closest('.spesifikasi-row').remove();
        }
    });

    // Sediakan satu baris kosong pertama.
    addRow();
})();
</script>
@endpush
@endsection
