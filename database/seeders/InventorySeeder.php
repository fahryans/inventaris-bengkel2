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
use App\Models\SpesifikasiAlat;
use App\Models\UnitAlat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InventorySeeder extends Seeder
{
    // Data master untuk generating realistis
    private array $kategoriAlat = [
        'Multimeter', 'Oscilloscope', 'Power Supply', 'Function Generator',
        'LCR Meter', 'Logic Analyzer', 'Solder Station', 'Hot Air Station',
        'CNC Machine', '3D Printer', 'Laser Cutter', 'Drill Machine',
        'Grinding Machine', 'Lathe Machine', 'Milling Machine', 'Plotter',
        'Crimping Tool', 'Wire Stripper', 'Tang Potong', 'Obeng Set',
    ];

    private array $kategoriBahan = [
        'Resistor', 'Kapasitor', 'Dioda', 'Transistor', 'IC',
        'LED', 'Kabel', 'Connector', 'PCB', 'Solder',
        'Flux', 'Kawat Tembaga', 'Termokopel', 'Thermal Paste',
        'Heat Shrink', 'Kabel Ribbon', 'Relay', 'Sensor', 'Baterai', 'Adaptor',
    ];

    private array $labs = [
        ['nama' => 'Laboratorium Elektronika', 'lokasi' => 'Gedung A Lantai 2'],
        ['nama' => 'Laboratorium Mekanik', 'lokasi' => 'Gedung B Lantai 1'],
        ['nama' => 'Laboratorium Komputer', 'lokasi' => 'Gedung C Lantai 3'],
        ['nama' => 'Laboratorium Kimia', 'lokasi' => 'Gedung D Lantai 1'],
        ['nama' => 'Laboratorium Fisika', 'lokasi' => 'Gedung E Lantai 2'],
    ];

    private array $mereks = [
        'Fluke', 'Tektronix', 'Agilent', 'Keysight', 'Rigol',
        'UNI-T', 'Hantek', 'FNIRSI', 'BSIDE', 'ZOYI',
        'Yihua', 'Fnirsi', 'JBC', 'Weller', 'Hakko',
        'Mazak', 'DMG Mori', 'Haas', 'Fanuc', 'Siemens',
        'Bosch', 'Makita', 'DeWalt', 'Milwaukee', 'Hitachi',
    ];

    private array $suppliers = [
        'PT Elektronik Indonesia', 'CV Komponen Elektronik', 'PT Teknik Mandiri',
        'CV Sumber Teknik', 'PT Mitra Elektronik', 'CV Jaya Teknik',
        'PT Abadi Sentosa', 'CV Berkat Abadi', 'PT Maju Jaya', 'CV Prima Teknik',
        'PT Sentosa Elektronik', 'CV Global Teknik', 'PT Nusantara Komponen',
        'CV Berkah Elektronik', 'PT Persada Teknik',
    ];

    private array $spesifikasiAlat = [
        // Multimeter
        ['kode' => 'MD-01', 'nama' => 'Digital Basic', 'deskripsi' => 'Multimeter digital standar'],
        ['kode' => 'MD-02', 'nama' => 'Digital Advanced', 'deskripsi' => 'Multimeter digital dengan fitur lengkap'],
        ['kode' => 'MA-01', 'nama' => 'Analog', 'deskripsi' => 'Multimeter analog klasik'],
        // Oscilloscope
        ['kode' => 'OS-50', 'nama' => '50MHz', 'deskripsi' => 'Oscilloscope 50MHz 2 channel'],
        ['kode' => 'OS-100', 'nama' => '100MHz', 'deskripsi' => 'Oscilloscope 100MHz 2 channel'],
        ['kode' => 'OS-200', 'nama' => '200MHz', 'deskripsi' => 'Oscilloscope 200MHz 4 channel'],
        // Power Supply
        ['kode' => 'PS-5V', 'nama' => '5V 2A', 'deskripsi' => 'Regulated power supply 5V'],
        ['kode' => 'PS-12V', 'nama' => '12V 3A', 'deskripsi' => 'Regulated power supply 12V'],
        ['kode' => 'PS-30V', 'nama' => '0-30V 5A', 'deskripsi' => 'Variable power supply 0-30V'],
        // Function Generator
        ['kode' => 'FG-10', 'nama' => '10MHz', 'deskripsi' => 'Function generator 10MHz'],
        ['kode' => 'FG-25', 'nama' => '25MHz', 'deskripsi' => 'Function generator 25MHz'],
        // LCR Meter
        ['kode' => 'LCR-01', 'nama' => 'Basic', 'deskripsi' => 'LCR meter standar'],
        // Logic Analyzer
        ['kode' => 'LA-16', 'nama' => '16 Channel', 'deskripsi' => 'Logic analyzer 16 channel'],
        ['kode' => 'LA-32', 'nama' => '32 Channel', 'deskripsi' => 'Logic analyzer 32 channel'],
        // Solder Station
        ['kode' => 'SS-60', 'nama' => '60W', 'deskripsi' => 'Solder station 60 watt'],
        ['kode' => 'SS-100', 'nama' => '100W', 'deskripsi' => 'Solder station 100 watt'],
        // Hot Air
        ['kode' => 'HA-01', 'nama' => 'Hot Air Basic', 'deskripsi' => 'Hot air rework station'],
        // CNC
        ['kode' => 'CNC-01', 'nama' => 'Mini CNC', 'deskripsi' => 'CNC router mini 3 axis'],
        ['kode' => 'CNC-02', 'nama' => 'Industrial CNC', 'deskripsi' => 'CNC router industri 4 axis'],
        // 3D Printer
        ['kode' => '3DP-01', 'nama' => 'FDM Basic', 'deskripsi' => '3D printer FDM entry level'],
        ['kode' => '3DP-02', 'nama' => 'FDM Pro', 'deskripsi' => '3D printer FDM professional'],
        // Drill
        ['kode' => 'DR-01', 'nama' => 'Hand Drill', 'deskripsi' => 'Mesin bor tangan'],
        ['kode' => 'DR-02', 'nama' => 'Bench Drill', 'deskripsi' => 'Mesin bor duduk'],
        // Grinder
        ['kode' => 'GR-01', 'nama' => 'Angle Grinder', 'deskripsi' => 'Mesin gerinda tangan'],
        ['kode' => 'GR-02', 'nama' => 'Bench Grinder', 'deskripsi' => 'Mesin gerinda duduk'],
        // Tang
        ['kode' => 'TP-UB', 'nama' => 'Ukuran Besar', 'deskripsi' => 'Tang potong ukuran besar'],
        ['kode' => 'TP-UK', 'nama' => 'Ukuran Kecil', 'deskripsi' => 'Tang potong ukuran kecil'],
        // Obeng
        ['kode' => 'OB-01', 'nama' => 'Phillips Set', 'deskripsi' => 'Set obeng Phillips'],
        ['kode' => 'OB-02', 'nama' => 'Flathead Set', 'deskripsi' => 'Set obeng flathead'],
    ];

    private array $spesifikasiBahan = [
        // Resistor
        ['kode' => 'R-100', 'nama' => '100 Ohm 1/4W', 'deskripsi' => 'Carbon film resistor 100 ohm'],
        ['kode' => 'R-1K', 'nama' => '1K Ohm 1/4W', 'deskripsi' => 'Carbon film resistor 1K ohm'],
        ['kode' => 'R-10K', 'nama' => '10K Ohm 1/4W', 'deskripsi' => 'Carbon film resistor 10K ohm'],
        ['kode' => 'R-100K', 'nama' => '100K Ohm 1/4W', 'deskripsi' => 'Carbon film resistor 100K ohm'],
        // Kapasitor
        ['kode' => 'C-10P', 'nama' => '10pF Ceramic', 'deskripsi' => 'Ceramic capacitor 10pF'],
        ['kode' => 'C-100N', 'nama' => '100nF Ceramic', 'deskripsi' => 'Ceramic capacitor 100nF'],
        ['kode' => 'C-10U', 'nama' => '10uF Electrolytic', 'deskripsi' => 'Electrolytic capacitor 10uF 25V'],
        ['kode' => 'C-100U', 'nama' => '100uF Electrolytic', 'deskripsi' => 'Electrolytic capacitor 100uF 25V'],
        // Dioda
        ['kode' => 'D-4007', 'nama' => '1N4007', 'deskripsi' => 'Rectifier diode 1A 1000V'],
        ['kode' => 'D-4148', 'nama' => '1N4148', 'deskripsi' => 'Signal diode 150mA'],
        // LED
        ['kode' => 'LED-R', 'nama' => 'LED Merah 5mm', 'deskripsi' => 'LED merah 5mm 2V'],
        ['kode' => 'LED-G', 'nama' => 'LED Hijau 5mm', 'deskripsi' => 'LED hijau 5mm 2.2V'],
        ['kode' => 'LED-B', 'nama' => 'LED Biru 5mm', 'deskripsi' => 'LED biru 5mm 3.3V'],
        // Kabel
        ['kode' => 'K-01', 'nama' => 'Kabel AWG22', 'deskripsi' => 'Kabel tembaga AWG22 per meter'],
        ['kode' => 'K-02', 'nama' => 'Kabel AWG18', 'deskripsi' => 'Kabel tembaga AWG18 per meter'],
        // Solder
        ['kode' => 'S-01', 'nama' => 'Solder Lead Free', 'deskripsi' => 'Solder lead free Sn99.3Cu0.7'],
        ['kode' => 'S-02', 'nama' => 'Solder Lead', 'deskripsi' => 'Solder Sn63Pb37'],
        // Flux
        ['kode' => 'FL-01', 'nama' => 'Flux Liquid', 'deskripsi' => 'Flux cair aktivator tinggi'],
        // PCB
        ['kode' => 'PCB-01', 'nama' => 'PCB Single Layer', 'deskripsi' => 'PCB prototipe single layer'],
        ['kode' => 'PCB-02', 'nama' => 'PCB Double Layer', 'deskripsi' => 'PCB prototipe double layer'],
    ];

    private array $keperluanPeminjaman = [
        'Praktikum Elektronika Dasar',
        'Praktikum Rangkaian Logika',
        'Praktikum Pengukuran',
        'Penelitian Skripsi',
        'Penelitian Tugas Akhir',
        'Perbaikan Alat Lab',
        'Demonstrasi Dosen',
        'Praktikum Mikroprosesor',
        'Praktikum Sistem Kendali',
        'Proyek Mahasiswa',
    ];

    private array $kondisiAlat = ['baik', 'rusak_ringan', 'rusak_berat'];

    private array $statusUnit = ['tersedia', 'dipinjam', 'maintenance'];

    public function run(): void
    {
        $this->seedKategori();
        $this->seedLaboratorium();
        $this->seedAlatDanSpesifikasi();
        $this->seedBahan();
        $this->seedPengadaanAlat();
        $this->seedPengadaanBahan();
        $this->seedPeminjamanAlat();
        $this->seedPemeliharaanAlat();
        $this->seedPemakaianBahan();
    }

    private function seedKategori(): void
    {
        foreach ($this->kategoriAlat as $kat) {
            Kategori::create(['nama_kategori' => $kat, 'jenis' => 'alat']);
        }

        foreach ($this->kategoriBahan as $kat) {
            Kategori::create(['nama_kategori' => $kat, 'jenis' => 'bahan']);
        }
    }

    private function seedLaboratorium(): void
    {
        $kalabs = User::where('role', 'kepala_labor')->get();

        foreach ($this->labs as $i => $lab) {
            Laboratorium::create([
                'id_user_kalab' => $kalabs[$i % $kalabs->count()]->id,
                'nama_labor' => $lab['nama'],
                'lokasi' => $lab['lokasi'],
                'sop' => 'SOP ' . str_replace('Laboratorium ', '', $lab['nama']),
            ]);
        }
    }

    private function seedAlatDanSpesifikasi(): void
    {
        $labs = Laboratorium::all();
        $kategoriAlat = Kategori::where('jenis', 'alat')->get();
        $tipePelacakan = ['agregat', 'unit'];

        // Buat 50 alat
        for ($i = 1; $i <= 50; $i++) {
            $kategori = $kategoriAlat->random();
            $lab = $labs->random();

            $alat = Alat::create([
                'id_kategori' => $kategori->id,
                'id_labor' => $lab->id,
                'nama_alat' => $kategori->nama_kategori . ' ' . $i,
                'tipe_pelacakan' => $tipePelacakan[array_rand($tipePelacakan)],
            ]);

            // Buat 1-3 spesifikasi per alat
            $spesifikasiCount = rand(1, 3);
            $usedSpecs = [];
            for ($j = 0; $j < $spesifikasiCount; $j++) {
                do {
                    $specIndex = array_rand($this->spesifikasiAlat);
                } while (in_array($specIndex, $usedSpecs));
                $usedSpecs[] = $specIndex;

                $spec = $this->spesifikasiAlat[$specIndex];
                SpesifikasiAlat::create([
                    'id_alat' => $alat->id,
                    'kode_spesifikasi' => $spec['kode'] . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'nama_spesifikasi' => $spec['nama'],
                    'deskripsi' => $spec['deskripsi'],
                ]);
            }
        }
    }

    private function seedBahan(): void
    {
        $labs = Laboratorium::all();
        $kategoriBahan = Kategori::where('jenis', 'bahan')->get();
        $satuan = ['pcs', 'ml', 'gram', 'meter', 'roll', 'set', 'botol'];

        // Buat 50 bahan
        for ($i = 1; $i <= 50; $i++) {
            $kategori = $kategoriBahan->random();
            $lab = $labs->random();

            Bahan::create([
                'id_kategori' => $kategori->id,
                'id_labor' => $lab->id,
                'nama_bahan' => $kategori->nama_kategori . ' ' . $i,
                'satuan' => $satuan[array_rand($satuan)],
                'spesifikasi' => $this->spesifikasiBahan[array_rand($this->spesifikasiBahan)]['deskripsi'],
            ]);
        }
    }

    private function seedPengadaanAlat(): void
    {
        $admin = User::where('no_induk', 'ADM001')->first();
        $alats = Alat::all();

        // Buat 200 pengadaan alat
        for ($i = 1; $i <= 200; $i++) {
            $alat = $alats->random();
            $spesifikasi = $alat->spesifikasiAlat->random();

            $tanggalPengadaan = now()->subMonths(rand(1, 24));
            $jumlah = $alat->tipe_pelacakan === 'unit' ? rand(1, 10) : rand(5, 100);
            $harga = rand(50000, 5000000);

            $pengadaan = PengadaanAlat::create([
                'id_alat' => $alat->id,
                'id_spesifikasi_alat' => $spesifikasi->id,
                'id_user_input' => $admin->id,
                'kode_inventaris' => $alat->tipe_pelacakan === 'agregat' 
                    ? strtoupper($spesifikasi->kode_spesifikasi) . '-' . $tanggalPengadaan->format('Ym') . '-' . str_pad($i, 3, '0', STR_PAD_LEFT)
                    : null,
                'tanggal_pengadaan' => $tanggalPengadaan,
                'harga_perolehan' => $harga,
                'jumlah' => $jumlah,
                'merek' => $this->mereks[array_rand($this->mereks)],
                'supplier' => $this->suppliers[array_rand($this->suppliers)],
                'tanggal_masuk' => rand(0, 1) ? $tanggalPengadaan->addDays(rand(1, 14)) : null,
            ]);

            // Auto-create unit alat for tipe unit
            if ($alat->tipe_pelacakan === 'unit') {
                for ($j = 1; $j <= $jumlah; $j++) {
                    UnitAlat::create([
                        'id_alat' => $alat->id,
                        'id_spesifikasi_alat' => $spesifikasi->id,
                        'kode_inventaris' => null,
                        'kondisi_saat_ini' => 'baik',
                        'status' => 'tersedia',
                    ]);
                }
            }
        }
    }

    private function seedPengadaanBahan(): void
    {
        $admin = User::where('no_induk', 'ADM001')->first();
        $bahans = Bahan::all();

        // Buat 200 pengadaan bahan
        for ($i = 1; $i <= 200; $i++) {
            $bahan = $bahans->random();

            $tanggalPengadaan = now()->subMonths(rand(1, 24));
            $jumlah = rand(10, 1000);
            // Pastikan stok tersisa > 0 untuk pemakaian
            $stokTersisa = rand(1, $jumlah);
            $harga = rand(10000, 500000);

            PengadaanBahan::create([
                'id_bahan' => $bahan->id,
                'id_user_input' => $admin->id,
                'tanggal_pengadaan' => $tanggalPengadaan,
                'harga_perolehan' => $harga,
                'jumlah' => $jumlah,
                'merek' => $this->mereks[array_rand($this->mereks)],
                'stok_tersisa_batch' => $stokTersisa,
                'masa_expire_bahan' => now()->addYears(rand(1, 3)),
                'supplier' => $this->suppliers[array_rand($this->suppliers)],
                'tanggal_masuk' => rand(0, 1) ? $tanggalPengadaan->addDays(rand(1, 7)) : null,
            ]);
        }
    }

    private function seedPeminjamanAlat(): void
    {
        $users = User::whereIn('role', ['mahasiswa', 'dosen'])->get();
        $alatAgregats = Alat::where('tipe_pelacakan', 'agregat')->get();
        $unitAlats = UnitAlat::all();

        // Buat 50 peminjaman
        for ($i = 1; $i <= 50; $i++) {
            $user = $users->random();
            $isUnit = rand(0, 1) && $unitAlats->isNotEmpty();

            $waktuPeminjaman = now()->subDays(rand(1, 90));
            $waktuPengembalian = $waktuPeminjaman->addDays(rand(1, 14));
            $sudahKembali = rand(0, 1);

            if ($isUnit) {
                $unit = $unitAlats->random();
                PeminjamanAlat::create([
                    'id_alat' => null,
                    'id_unit_alat' => $unit->id,
                    'id_spesifikasi_alat' => $unit->id_spesifikasi_alat,
                    'id_user_peminjam' => $user->id,
                    'keperluan' => $this->keperluanPeminjaman[array_rand($this->keperluanPeminjaman)],
                    'waktu_peminjaman' => $waktuPeminjaman,
                    'waktu_pengembalian' => $waktuPengembalian,
                    'waktu_kembali_aktual' => $sudahKembali ? $waktuPengembalian->subDays(rand(0, 2)) : null,
                    'jumlah' => 1,
                    'kondisi_saat_peminjaman' => 'baik',
                    'kondisi_saat_pengembalian' => $sudahKembali ? 'baik' : null,
                    'status' => $sudahKembali ? 'sudah_dikembalikan' : 'terpinjam',
                ]);
            } else {
                $alat = $alatAgregats->random();
                $spesifikasi = $alat->spesifikasiAlat->random();
                $jumlah = rand(1, 5);

                PeminjamanAlat::create([
                    'id_alat' => $alat->id,
                    'id_unit_alat' => null,
                    'id_spesifikasi_alat' => $spesifikasi->id,
                    'id_user_peminjam' => $user->id,
                    'keperluan' => $this->keperluanPeminjaman[array_rand($this->keperluanPeminjaman)],
                    'waktu_peminjaman' => $waktuPeminjaman,
                    'waktu_pengembalian' => $waktuPengembalian,
                    'waktu_kembali_aktual' => $sudahKembali ? $waktuPengembalian->subDays(rand(0, 2)) : null,
                    'jumlah' => $jumlah,
                    'kondisi_saat_peminjaman' => 'baik',
                    'kondisi_saat_pengembalian' => $sudahKembali ? 'baik' : null,
                    'status' => $sudahKembali ? 'sudah_dikembalikan' : 'terpinjam',
                ]);
            }
        }
    }

    private function seedPemeliharaanAlat(): void
    {
        $teknisis = User::where('role', 'teknisi')->get();
        $unitAlats = UnitAlat::all();

        if ($unitAlats->isEmpty()) return;

        // Buat 30 pemeliharaan
        for ($i = 1; $i <= 30; $i++) {
            $unit = $unitAlats->random();
            $teknisi = $teknisis->random();
            $tanggalCek = now()->subMonths(rand(1, 12));

            PemeliharaanAlat::create([
                'id_unit_alat' => $unit->id,
                'id_teknisi' => $teknisi->id,
                'tanggal_cek' => $tanggalCek,
                'tanggal_cek_berikutnya' => $tanggalCek->addMonths(rand(1, 6)),
                'kondisi' => $this->kondisiAlat[array_rand($this->kondisiAlat)],
                'biaya' => rand(0, 500000),
                'detail_biaya' => 'Pengecekan rutin',
                'catatan' => 'Pengecekan dan perawatan berkala',
                'hasil_pemeliharaan' => rand(0, 1) ? 'Lulus' : 'Perlu Perbaikan',
            ]);
        }
    }

    private function seedPemakaianBahan(): void
    {
        $users = User::whereIn('role', ['mahasiswa', 'dosen', 'teknisi'])->get();
        $bahans = Bahan::all();
        $pengadaans = PengadaanBahan::where('stok_tersisa_batch', '>', 0)->get();

        if ($bahans->isEmpty() || $pengadaans->isEmpty()) return;

        // Buat 50 pemakaian
        for ($i = 1; $i <= 50; $i++) {
            $user = $users->random();
            $bahan = $bahans->random();
            
            // Ambil pengadaan yang stok tersisa > 0 untuk bahan ini
            $availablePengadaans = $pengadaans->where('id_bahan', $bahan->id);
            
            if ($availablePengadaans->isEmpty()) {
                continue; // Skip jika tidak ada stok tersisa untuk bahan ini
            }
            
            $pengadaan = $availablePengadaans->random();

            $jumlahAmbil = rand(1, min(20, $pengadaan->stok_tersisa_batch));
            $jumlahTerpakai = rand(0, $jumlahAmbil);
            $jumlahKembali = $jumlahAmbil - $jumlahTerpakai;

            PemakaianBahan::create([
                'id_bahan' => $bahan->id,
                'id_pengadaan_bahan' => $pengadaan->id,
                'id_user_pemakai' => $user->id,
                'id_user_verifikasi' => User::where('role', 'kepala_labor')->first()->id ?? null,
                'keperluan' => $this->keperluanPeminjaman[array_rand($this->keperluanPeminjaman)],
                'jumlah_pengambilan' => $jumlahAmbil,
                'jumlah_terpakai' => $jumlahTerpakai,
                'jumlah_pengembalian' => $jumlahKembali,
                'waktu_pemakaian' => now()->subDays(rand(1, 90)),
            ]);
        }
    }
}
