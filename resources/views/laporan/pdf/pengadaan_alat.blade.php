@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Alat</th>
            <th>Jumlah</th>
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
            <td>{{ $item->alat->nama_alat }}</td>
            <td>{{ $item->jumlah }}</td>
            <td>Rp {{ number_format($item->harga_perolehan, 0, ',', '.') }}</td>
            <td>{{ $item->tanggal_pengadaan->format('d/m/Y') }}</td>
            <td>{{ $item->supplier ?? '-' }}</td>
            <td>{{ $item->tanggal_masuk ? 'Sudah Diterima' : 'Menunggu' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
