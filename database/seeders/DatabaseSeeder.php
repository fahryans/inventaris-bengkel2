<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\PemakaianBahan;
use App\Models\PemeliharaanAlat;
use App\Models\PeminjamanAlat;
use App\Models\PengadaanAlat;
use App\Models\PengadaanBahan;
use App\Models\UnitAlat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedKategori();
        $this->seedLaboratorium();
        $this->seedAlat();
        $this->seedBahan();
        $this->seedPengadaanAlat();
        $this->seedPengadaanBahan();
        $this->seedUnitAlat();
        $this->seedPeminjamanAlat();
        $this->seedPemeliharaanAlat();
        $this->seedPemakaianBahan();
    }

    private function seedUsers(): void
    {
        User::create([
            'role' => 'admin_jurusan',
            'nama' => 'Admin Jurusan',
            'email' => 'admin@inventaris.test',
            'password' => Hash::make('password'),
            'status' => 'aktif',
            'no_hp' => '081234567890',
            'no_induk' => 'ADM001',
        ]);

        User::create([
            'role' => 'kadep',
            'nama' => 'Kepala Departemen',
            'email' => 'kadep@inventaris.test',
            'password' => Hash::make('password'),
            'status' => 'aktif',
            'no_hp' => '081234567891',
            'no_induk' => 'KAD001',
        ]);

        User::create([
            'role' => 'kepala_labor',
            'nama' => 'Kepala Lab Elektronika',
            'email' => 'kalab1@inventaris.test',
            'password' => Hash::make('password'),
            'status' => 'aktif',
            'no_hp' => '081234567892',
            'no_induk' => 'KL001',
        ]);

        User::create([
            'role' => 'kepala_labor',
            'nama' => 'Kepala Lab Mekanik',
            'email' => 'kalab2@inventaris.test',
            'password' => Hash::make('password'),
            'status' => 'aktif',
            'no_hp' => '081234567893',
            'no_induk' => 'KL002',
        ]);

        User::create([
            'role' => 'teknisi',
            'nama' => 'Teknisi 1',
            'email' => 'teknisi1@inventaris.test',
            'password' => Hash::make('password'),
            'status' => 'aktif',
            'no_hp' => '081234567894',
            'no_induk' => 'TK001',
        ]);

        User::create([
            'role' => 'teknisi',
            'nama' => 'Teknisi 2',
            'email' => 'teknisi2@inventaris.test',
            'password' => Hash::make('password'),
            'status' => 'aktif',
            'no_hp' => '081234567895',
            'no_induk' => 'TK002',
        ]);

        User::create([
            'role' => 'dosen',
            'nama' => 'Dosen Elektronika',
            'email' => 'dosen1@inventaris.test',
            'password' => Hash::make('password'),
            'status' => 'aktif',
            'no_hp' => '081234567896',
            'no_induk' => 'DS001',
        ]);

        User::create([
            'role' => 'mahasiswa',
            'nama' => 'Mahasiswa Test',
            'email' => 'mahasiswa@inventaris.test',
            'password' => Hash::make('password'),
            'status' => 'aktif',
            'no_hp' => '081234567897',
            'no_induk' => 'MH001',
        ]);
    }

    private function seedKategori(): void
    {
        Kategori::create(['nama_kategori' => 'Multimeter', 'jenis' => 'alat']);
        Kategori::create(['nama_kategori' => 'Oscilloscope', 'jenis' => 'alat']);
        Kategori::create(['nama_kategori' => 'Power Supply', 'jenis' => 'alat']);
        Kategori::create(['nama_kategori' => 'Resistor', 'jenis' => 'bahan']);
        Kategori::create(['nama_kategori' => 'Kapasitor', 'jenis' => 'bahan']);
        Kategori::create(['nama_kategori' => 'Dioda', 'jenis' => 'bahan']);
    }

    private function seedLaboratorium(): void
    {
        $kalab1 = User::where('no_induk', 'KL001')->first();
        $kalab2 = User::where('no_induk', 'KL002')->first();

        Laboratorium::create([
            'id_user_kalab' => $kalab1->id,
            'nama_labor' => 'Laboratorium Elektronika',
            'lokasi' => 'Gedung A Lantai 2',
            'sop' => 'SOP Elektronika',
        ]);

        Laboratorium::create([
            'id_user_kalab' => $kalab2->id,
            'nama_labor' => 'Laboratorium Mekanik',
            'lokasi' => 'Gedung B Lantai 1',
            'sop' => 'SOP Mekanik',
        ]);
    }

    private function seedAlat(): void
    {
        $lab1 = Laboratorium::first();
        $katAlat = Kategori::where('jenis', 'alat')->pluck('id')->toArray();

        Alat::create([
            'id_kategori' => $katAlat[0],
            'id_labor' => $lab1->id,
            'nama_alat' => 'Multimeter Digital',
            'merek' => 'Fluke',
            'spesifikasi' => 'Digital Multimeter 87 V',
            'tipe_pelacakan' => 'agregat',
            'jumlah_alat' => 0,
        ]);

        Alat::create([
            'id_kategori' => $katAlat[1],
            'id_labor' => $lab1->id,
            'nama_alat' => 'Oscilloscope',
            'merek' => 'Tektronix',
            'spesifikasi' => '100 MHz Digital Oscilloscope',
            'tipe_pelacakan' => 'unit',
            'jumlah_alat' => 0,
        ]);

        Alat::create([
            'id_kategori' => $katAlat[2],
            'id_labor' => $lab1->id,
            'nama_alat' => 'Power Supply',
            'merek' => 'Agilent',
            'spesifikasi' => '0-30V, 0-3A',
            'tipe_pelacakan' => 'unit',
            'jumlah_alat' => 0,
        ]);
    }

    private function seedBahan(): void
    {
        $lab1 = Laboratorium::first();
        $katBahan = Kategori::where('jenis', 'bahan')->pluck('id')->toArray();

        Bahan::create([
            'id_kategori' => $katBahan[0],
            'id_labor' => $lab1->id,
            'nama_bahan' => 'Resistor 1K',
            'stok_saat_ini' => 60,
            'stok_minimum' => 50,
            'satuan' => 'pcs',
            'merek' => 'Generic',
            'spesifikasi' => '1/4W Carbon Film',
        ]);

        Bahan::create([
            'id_kategori' => $katBahan[1],
            'id_labor' => $lab1->id,
            'nama_bahan' => 'Kapasitor 10µF',
            'stok_saat_ini' => 30,
            'stok_minimum' => 20,
            'satuan' => 'pcs',
            'merek' => 'Generic',
            'spesifikasi' => '50V Electrolytic',
        ]);

        Bahan::create([
            'id_kategori' => $katBahan[2],
            'id_labor' => $lab1->id,
            'nama_bahan' => 'Dioda 1N4007',
            'stok_saat_ini' => 15,
            'stok_minimum' => 30,
            'satuan' => 'pcs',
            'merek' => 'Generic',
            'spesifikasi' => '1A/1000V Rectifier',
        ]);
    }

    private function seedPengadaanAlat(): void
    {
        $user = User::where('no_induk', 'ADM001')->first();
        $alat = Alat::first();

        $pengadaan = PengadaanAlat::create([
            'id_alat' => $alat->id,
            'id_user_input' => $user->id,
            'tanggal_pengadaan' => now()->subMonths(2),
            'harga_perolehan' => 500000,
            'jumlah' => 5,
            'supplier' => 'PT Elektronik Indonesia',
            'tanggal_masuk' => now()->subMonths(2)->addDays(5),
        ]);

        $alat->update(['jumlah_alat' => $alat->jumlah_alat + $pengadaan->jumlah]);
    }

    private function seedPengadaanBahan(): void
    {
        $user = User::where('no_induk', 'ADM001')->first();
        $bahan = Bahan::first();

        PengadaanBahan::create([
            'id_bahan' => $bahan->id,
            'id_user_input' => $user->id,
            'tanggal_pengadaan' => now()->subMonth(),
            'harga_perolehan' => 50000,
            'jumlah' => 500,
            'stok_tersisa_batch' => 60,
            'masa_expire_bahan' => now()->addYears(2),
            'supplier' => 'CV Komponen Elektronik',
            'tanggal_masuk' => now()->subMonth()->addDays(3),
        ]);
    }

    private function seedUnitAlat(): void
    {
        $alat = Alat::where('tipe_pelacakan', 'unit')->first();

        for ($i = 1; $i <= 3; $i++) {
            UnitAlat::create([
                'id_alat' => $alat->id,
                'kode_inventaris' => 'OS-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'kondisi_saat_ini' => 'Baik',
                'status' => 'tersedia',
            ]);
        }
    }

    private function seedPeminjamanAlat(): void
    {
        $mahasiswa = User::where('no_induk', 'MH001')->first();
        $alatAgregat = Alat::where('tipe_pelacakan', 'agregat')->first();

        PeminjamanAlat::create([
            'id_alat' => $alatAgregat->id,
            'id_unit_alat' => null,
            'id_user_peminjam' => $mahasiswa->id,
            'keperluan' => 'Praktik Elektronika Dasar',
            'waktu_peminjaman' => now()->subDays(3),
            'waktu_pengembalian' => now()->addDays(2),
            'waktu_kembali_aktual' => null,
            'jumlah' => 2,
            'kondisi_saat_peminjaman' => 'Baik',
            'kondisi_saat_pengembalian' => null,
            'status' => 'terpinjam',
        ]);

        $alatAgregat->update(['jumlah_alat' => $alatAgregat->jumlah_alat - 2]);

        $unitAlat = UnitAlat::first();
        $unitAlat->update(['status' => 'dipinjam']);

        PeminjamanAlat::create([
            'id_alat' => null,
            'id_unit_alat' => $unitAlat->id,
            'id_user_peminjam' => $mahasiswa->id,
            'keperluan' => 'Praktik Pengukuran Sinyal',
            'waktu_peminjaman' => now()->subDays(5),
            'waktu_pengembalian' => now()->addDays(2),
            'waktu_kembali_aktual' => null,
            'jumlah' => 1,
            'kondisi_saat_peminjaman' => 'Baik',
            'kondisi_saat_pengembalian' => null,
            'status' => 'terpinjam',
        ]);
    }

    private function seedPemeliharaanAlat(): void
    {
        $teknisi = User::where('no_induk', 'TK001')->first();
        $unitAlat = UnitAlat::first();

        PemeliharaanAlat::create([
            'id_unit_alat' => $unitAlat->id,
            'id_teknisi' => $teknisi->id,
            'tanggal_cek' => now()->subMonths(2),
            'tanggal_cek_berikutnya' => now()->addMonths(1),
            'kondisi' => 'Baik',
            'biaya' => 0,
            'detail_biaya' => 'Pengecekan rutin',
            'catatan' => 'Alat dalam kondisi normal',
            'hasil_pemeliharaan' => 'Lulus',
        ]);
    }

    private function seedPemakaianBahan(): void
    {
        $user = User::where('no_induk', 'DS001')->first();
        $bahan = Bahan::first();
        $pengadaan = PengadaanBahan::first();

        PemakaianBahan::create([
            'id_bahan' => $bahan->id,
            'id_pengadaan_bahan' => $pengadaan->id,
            'id_user_pemakai' => $user->id,
            'id_user_verifikasi' => User::where('no_induk', 'KL001')->first()->id,
            'keperluan' => 'Praktik Rangkaian Dasar',
            'jumlah_pengambilan' => 50,
            'jumlah_terpakai' => 40,
            'jumlah_pengembalian' => 10,
            'waktu_pemakaian' => now()->subDays(2),
        ]);
    }
}
