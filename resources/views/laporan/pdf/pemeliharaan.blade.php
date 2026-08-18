@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Unit Alat</th>
            <th>Teknisi</th>
            <th>Tanggal Cek</th>
            <th>Kondisi</th>
            <th>Biaya</th>
            <th>Hasil</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->unitAlat->kode_inventaris }}</td>
            <td>{{ $item->teknisi->nama }}</td>
            <td>{{ $item->tanggal_cek->format('d/m/Y') }}</td>
            <td>{{ $item->kondisi ?? '-' }}</td>
            <td>Rp {{ number_format($item->biaya ?? 0, 0, ',', '.') }}</td>
            <td>{{ $item->hasil_pemeliharaan ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
