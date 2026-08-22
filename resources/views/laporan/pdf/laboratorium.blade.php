@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Laboratorium</th>
            <th>Lokasi</th>
            <th>Kepala Lab</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama_labor }}</td>
            <td>{{ $item->lokasi ?? '-' }}</td>
            <td>{{ $item->kalab->nama ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
