@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Alat</th>
            <th>Merek</th>
            <th>Kategori</th>
            <th>Laboratorium</th>
            <th>Tipe Pelacakan</th>
            <th>Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama_alat }}</td>
            <td>{{ $item->merek ?? '-' }}</td>
            <td>{{ $item->kategori->nama_kategori }}</td>
            <td>{{ $item->laboratorium->nama_labor }}</td>
            <td>{{ ucfirst($item->tipe_pelacakan) }}</td>
            <td>{{ $item->getAvailableQuantity() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
