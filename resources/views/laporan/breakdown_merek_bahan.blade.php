@extends('layouts.admin')

@section('title', 'Laporan Breakdown Bahan per Merek')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('laporan.index') }}">Laporan</a></li>
            <li class="breadcrumb-item active">Breakdown Bahan per Merek</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-[#5b202f] text-[#f5f0e9] d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan Breakdown Bahan per Merek & Supplier</h5>
            <button onclick="window.print()" class="btn btn-sm btn-light">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Spesifikasi</th>
                            <th>Satuan</th>
                            <th>Merek</th>
                            <th>Supplier</th>
                            <th>Jumlah</th>
                            <th>Stok Tersisa</th>
                            <th>Harga Perolehan</th>
                            <th>Total Harga</th>
                            <th>Masa Expire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotal = 0;
                            $groupedData = [];
                        @endphp
                        
                        @forelse($pengadaanBahan as $pengadaan)
                            @php
                                $key = $pengadaan->bahan->id;
                                if (!isset($groupedData[$key])) {
                                    $groupedData[$key] = [
                                        'nama_bahan' => $pengadaan->bahan->nama_bahan,
                                        'spesifikasi' => $pengadaan->bahan->spesifikasi,
                                        'satuan' => $pengadaan->bahan->satuan,
                                        'items' => []
                                    ];
                                }
                                $groupedData[$key]['items'][] = $pengadaan;
                            @endphp
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Tidak ada data pengadaan bahan
                                </td>
                            </tr>
                        @endforelse

                        @foreach($groupedData as $group)
                            @php $firstRow = true; $subtotal = 0; $totalStok = 0; @endphp
                            @foreach($group['items'] as $item)
                                @php
                                    $itemTotal = $item->harga_perolehan * $item->jumlah;
                                    $subtotal += $itemTotal;
                                    $totalStok += $item->stok_tersisa_batch;
                                    $grandTotal += $itemTotal;
                                @endphp
                                <tr>
                                    @if($firstRow)
                                        <td rowspan="{{ count($group['items']) }}"><strong>{{ $group['nama_bahan'] }}</strong></td>
                                        <td rowspan="{{ count($group['items']) }}">{{ $group['spesifikasi'] ?? '-' }}</td>
                                        <td rowspan="{{ count($group['items']) }}">{{ $group['satuan'] }}</td>
                                        @php $firstRow = false; @endphp
                                    @endif
                                    <td>{{ $item->merek }}</td>
                                    <td>{{ $item->supplier }}</td>
                                    <td>{{ $item->jumlah }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->stok_tersisa_batch > 0 ? 'success' : 'danger' }}">
                                            {{ $item->stok_tersisa_batch }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($item->harga_perolehan, 2, ',', '.') }}</td>
                                    <td>Rp {{ number_format($itemTotal, 2, ',', '.') }}</td>
                                    <td>{{ $item->masa_expire_bahan?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-active fw-bold">
                                <td colspan="6">Subtotal {{ $group['nama_bahan'] }} (Stok Tersisa: {{ $totalStok }}):</td>
                                <td colspan="3">Rp {{ number_format($subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach

                        @if($pengadaanBahan->count() > 0)
                            <tr class="table-warning fw-bold">
                                <td colspan="6">TOTAL SEMUA PENGADAAN BAHAN:</td>
                                <td colspan="3">Rp {{ number_format($grandTotal, 2, ',', '.') }}</td>
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
