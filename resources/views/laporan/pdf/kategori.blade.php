@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Kategori</th>
            <th>Jenis</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama_kategori }}</td>
            <td>{{ ucfirst($item->jenis) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
