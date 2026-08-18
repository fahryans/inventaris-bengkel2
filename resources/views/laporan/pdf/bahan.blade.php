@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Bahan</th>
            <th>Kategori</th>
            <th>Stok Saat Ini</th>
            <th>Stok Minimum</th>
            <th>Satuan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama_bahan }}</td>
            <td>{{ $item->kategori->nama_kategori }}</td>
            <td>{{ $item->stok_saat_ini }}</td>
            <td>{{ $item->stok_minimum }}</td>
            <td>{{ $item->satuan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
