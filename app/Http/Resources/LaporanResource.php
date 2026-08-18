<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PeminjamanAlat;
use App\Models\PengadaanAlat;
use App\Models\PemeliharaanAlat;
use App\Models\PemakaianBahan;

class LaporanResource extends JsonResource
{
    public function toArray($request): array
    {
        $item = data_get($this->resource, 'first()') ?? data_get($this->resource, [0]) ?? $this->resource;

        $base = [
            'id' => $item->id,
        ];

        $type = class_basename($item);

        $specials = match($type) {
            'peminjamanalat' => [
                'nama_alat' => $item->alat?->nama_alat,
                'nama_user' => $item->userPeminjam?->nama,
                'waktu_peminjaman' => $item->waktu_peminjaman,
                'waktu_pengembalian' => $item->waktu_pengembalian,
                'status' => $item->status,
                'keperluan' => $item->keperluan,
                'jumlah' => $item->jumlah,
            ],
            'pengadaanalat' => [
                'nama_alat' => $item->alat?->nama_alat,
                'nama_user' => $item->userInput?->nama,
                'tanggal_pengadaan' => $item->tanggal_pengadaan,
                'harga_perolehan' => $item->harga_perolehan,
                'supplier' => $item->supplier,
                'jumlah' => $item->jumlah,
            ],
            'pemeliharaanalat' => [
                'nama_alat' => $item->unitAlat?->alat?->nama_alat,
                'nama_teknisi' => $item->teknisi?->nama,
                'tanggal_cek' => $item->tanggal_cek,
                'kondisi' => $item->kondisi,
                'biaya' => $item->biaya,
                'hasil_pemeliharaan' => $item->hasil_pemeliharaan,
            ],
            'pemakaianbahan' => [
                'nama_bahan' => $item->bahan?->nama_bahan,
                'nama_user_pemakai' => $item->userPemakai?->nama,
                'nama_user_verifikasi' => $item->userVerifikasi?->nama,
                'waktu_pemakaian' => $item->waktu_pemakaian,
                'keperluan' => $item->keperluan,
                'jumlah_pengambilan' => $item->jumlah_pengambilan,
                'jumlah_terpakai' => $item->jumlah_terpakai,
            ],
            default => [],
        };

        return array_merge($base, $specials);
    }
}