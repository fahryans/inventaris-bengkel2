@extends('layouts.admin')

@section('title', 'Laporan Breakdown Alat per Merek')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
            <li class="breadcrumb-item active">Breakdown Alat per Merek</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9] d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan Breakdown Alat per Merek & Supplier</h5>
            <button onclick="window.print()" class="btn btn-sm btn-light">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Alat</th>
                            <th>Spesifikasi</th>
                            <th>Merek</th>
                            <th>Supplier</th>
                            <th>Jumlah</th>
                            <th>Harga Perolehan</th>
                            <th>Total Harga</th>
                            <th>Tanggal Pengadaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotal = 0;
                            $groupedData = [];
                        @endphp
                        
                        @forelse($pengadaanAlat as $pengadaan)
                            @php
                                $key = $pengadaan->alat->id;
                                if (!isset($groupedData[$key])) {
                                    $groupedData[$key] = [
                                        'nama_alat' => $pengadaan->alat->nama_alat,
                                        'spesifikasi' => $pengadaan->alat->spesifikasi,
                                        'items' => []
                                    ];
                                }
                                $groupedData[$key]['items'][] = $pengadaan;
                            @endphp
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Tidak ada data pengadaan alat
                                </td>
                            </tr>
                        @endforelse

                        @foreach($groupedData as $group)
                            @php $firstRow = true; $subtotal = 0; @endphp
                            @foreach($group['items'] as $item)
                                @php
                                    $itemTotal = $item->harga_perolehan * $item->jumlah;
                                    $subtotal += $itemTotal;
                                    $grandTotal += $itemTotal;
                                @endphp
                                <tr>
                                    @if($firstRow)
                                        <td rowspan="{{ count($group['items']) }}"><strong>{{ $group['nama_alat'] }}</strong></td>
                                        <td rowspan="{{ count($group['items']) }}">{{ $group['spesifikasi'] ?? '-' }}</td>
                                        @php $firstRow = false; @endphp
                                    @endif
                                    <td>{{ $item->merek }}</td>
                                    <td>{{ $item->supplier }}</td>
                                    <td>{{ $item->jumlah }}</td>
                                    <td>Rp {{ number_format($item->harga_perolehan, 2, ',', '.') }}</td>
                                    <td>Rp {{ number_format($itemTotal, 2, ',', '.') }}</td>
                                    <td>{{ $item->tanggal_pengadaan->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-active fw-bold">
                                <td colspan="5">Subtotal {{ $group['nama_alat'] }}:</td>
                                <td colspan="2">Rp {{ number_format($subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach

                        @if($pengadaanAlat->count() > 0)
                            <tr class="table-warning fw-bold">
                                <td colspan="5">TOTAL SEMUA PENGADAAN ALAT:</td>
                                <td colspan="2">Rp {{ number_format($grandTotal, 2, ',', '.') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
