@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Alat</th>
            <th>Unit</th>
            <th>Peminjam</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Kembali</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->alat->nama_alat ?? '-' }}</td>
            <td>{{ $item->unitAlat->kode_inventaris ?? '-' }}</td>
            <td>{{ $item->userPeminjam->nama }}</td>
            <td>{{ $item->waktu_peminjaman->format('d/m/Y H:i') }}</td>
            <td>{{ $item->waktu_pengembalian?->format('d/m/Y H:i') ?? '-' }}</td>
            <td>{{ $item->status === 'terpinjam' ? 'Dipinjam' : 'Sudah Dikembalikan' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
