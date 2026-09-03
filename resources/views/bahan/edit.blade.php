@extends('layouts.admin')

@section('title', 'Edit Bahan')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bahan.index') }}">Data Bahan</a></li>
            <li class="breadcrumb-item active">Edit Bahan</li>
        </ol>
    </nav>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi Kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9]">
            <h5 class="mb-0">Form Edit Bahan</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('bahan.update', $bahan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori" id="id_kategori" class="form-select @error('id_kategori') is-invalid @enderror" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" {{ old('id_kategori', $bahan->id_kategori) == $kat->id ? 'selected' : '' }}>
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
                                        <option value="{{ $lab->id }}" {{ old('id_labor', $bahan->id_labor) == $lab->id ? 'selected' : '' }}>
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
                    <label for="nama_bahan" class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_bahan" id="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror" 
                           value="{{ old('nama_bahan', $bahan->nama_bahan) }}" required>
                    @error('nama_bahan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" id="satuan" class="form-control @error('satuan') is-invalid @enderror" 
                                   value="{{ old('satuan', $bahan->satuan) }}" placeholder="ml, gram, pcs, dll" required>
                            @error('satuan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="stok_minimum" class="form-label">Stok Minimum <span class="text-danger">*</span></label>
                            <input type="number" name="stok_minimum" id="stok_minimum" class="form-control @error('stok_minimum') is-invalid @enderror" 
                                   value="{{ old('stok_minimum', $bahan->stok_minimum) }}" min="0" placeholder="0" required>
                            @error('stok_minimum')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="foto" class="form-label">Foto</label>
                    @if($bahan->foto)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $bahan->foto) }}" alt="Foto Bahan" class="img-thumbnail" style="max-width: 200px;"
                                 onerror="this.onerror=null; this.src='{{ asset('img/no-image.svg') }}'; this.alt='Foto tidak tersedia';">
                        </div>
                    @endif
                    <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, GIF, WEBP, SVG, dll (Max 5MB)</small>
                    @error('foto')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-12">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Spesifikasi Bahan ({{ $bahan->spesifikasiBahan->count() }})</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddSpesifikasi">
                                <i class="fas fa-plus"></i> Tambah Spesifikasi
                            </button>
                        </div>
                        <p class="text-muted small">Tambahkan atau edit spesifikasi bahan di sini.</p>

                        <div id="spesifikasiContainer"></div>

                        @error('spesifikasi')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
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

@push('js')
<script>
(function () {
    const container = document.getElementById('spesifikasiContainer');
    // Load existing spesifikasi dari server
    const existingSpesifikasi = {!! json_encode($bahan->spesifikasiBahan->map(fn($s) => ['kode_spesifikasi' => $s->kode_spesifikasi, 'nama_spesifikasi' => $s->nama_spesifikasi, 'deskripsi' => $s->deskripsi])) !!};
    let index = existingSpesifikasi.length;

    function rowHtml(i, data) {
        const kode = data ? data.kode_spesifikasi : '';
        const nama = data ? data.nama_spesifikasi : '';
        const deskripsi = data ? data.deskripsi : '';
        return '<div class="row g-3 mb-3 spesifikasi-row align-items-center">'
            + '<div class="col-md-3">'
            + '<input type="text" name="spesifikasi[' + i + '][kode_spesifikasi]" class="form-control" placeholder="Kode Spesifikasi (mis: SPEC-01)" value="' + kode + '" required>'
            + '</div>'
            + '<div class="col-md-3">'
            + '<input type="text" name="spesifikasi[' + i + '][nama_spesifikasi]" class="form-control" placeholder="Nama Spesifikasi (mis: Grade A)" value="' + nama + '" required>'
            + '</div>'
            + '<div class="col-md-5">'
            + '<input type="text" name="spesifikasi[' + i + '][deskripsi]" class="form-control" placeholder="Deskripsi (opsional)" value="' + deskripsi + '">'
            + '</div>'
            + '<div class="col-md-1 text-end">'
            + '<button type="button" class="btn btn-outline-danger btn-sm btn-remove-spesifikasi" title="Hapus"><i class="fas fa-trash"></i></button>'
            + '</div>'
            + '</div>';
    }

    function addRow(data) {
        container.insertAdjacentHTML('beforeend', rowHtml(index, data));
        index++;
    }

    document.getElementById('btnAddSpesifikasi').addEventListener('click', function() {
        addRow(null);
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-spesifikasi')) {
            e.preventDefault();
            e.target.closest('.spesifikasi-row').remove();
        }
    });

    // Load semua spesifikasi existing sebagai baris
    existingSpesifikasi.forEach(function(data) {
        addRow(data);
    });

    // Jika tidak ada spesifikasi, sediakan satu baris kosong
    if (existingSpesifikasi.length === 0) {
        addRow(null);
    }
})();
</script>
@endpush
@endsection
