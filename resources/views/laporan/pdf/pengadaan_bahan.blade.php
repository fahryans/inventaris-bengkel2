@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Bahan</th>
            <th>Jumlah</th>
            <th>Stok Tersisa</th>
            <th>Harga Perolehan</th>
            <th>Tanggal Pengadaan</th>
            <th>Supplier</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->bahan->nama_bahan }}</td>
            <td>{{ $item->jumlah }}</td>
            <td>{{ $item->stok_tersisa_batch }}</td>
            <td>Rp {{ number_format($item->harga_perolehan, 0, ',', '.') }}</td>
            <td>{{ $item->tanggal_pengadaan->format('d/m/Y') }}</td>
            <td>{{ $item->supplier ?? '-' }}</td>
            <td>{{ $item->tanggal_masuk ? 'Sudah Diterima' : 'Menunggu' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
