@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Bahan</th>
            <th>Jumlah Diambil</th>
            <th>Jumlah Terpakai</th>
            <th>Pemakai</th>
            <th>Verifikator</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->bahan->nama_bahan }}</td>
            <td>{{ $item->jumlah_pengambilan }}</td>
            <td>{{ $item->jumlah_terpakai ?? '-' }}</td>
            <td>{{ $item->userPemakai->nama }}</td>
            <td>{{ $item->userVerifikasi?->nama ?? '-' }}</td>
            <td>{{ $item->waktu_pemakaian->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
