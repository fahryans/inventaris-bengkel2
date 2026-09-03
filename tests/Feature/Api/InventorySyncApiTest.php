<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Alat;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\PengadaanBahan;
use App\Models\PemakaianBahan;
use App\Models\PengadaanAlat;
use Laravel\Sanctum\Sanctum;

class InventorySyncApiTest extends TestCase
{
    private function adminToken(): User
    {
        $user = User::factory()->create(['role' => 'admin_jurusan']);
        Sanctum::actingAs($user);
        return $user;
    }

    public function test_api_login_rejects_inactive_user()
    {
        User::factory()->create(['email' => 'nonaktif@test.com', 'status' => 'tidak_aktif']);

        $response = $this->postJson('/api/login', [
            'email' => 'nonaktif@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_api_store_peminjaman_decrements_aggregate_stock()
    {
        $this->adminToken();
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $lab = Laboratorium::factory()->create();
        $alat = Alat::factory()->create([
            'tipe_pelacakan' => 'agregat',
            'id_kategori' => $kategori->id,
            'id_labor' => $lab->id,
        ]);
        PengadaanAlat::factory()->create(['id_alat' => $alat->id, 'jumlah' => 10, 'tanggal_masuk' => now()]);

        $response = $this->postJson('/api/peminjaman', [
            'id_alat' => $alat->id,
            'keperluan' => 'Praktikum',
            'waktu_peminjaman' => now()->format('Y-m-d H:i'),
            'kondisi_saat_peminjaman' => 'baik',
            'jumlah' => 3,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(7, $alat->fresh()->getAvailableQuantity());
        $this->assertEquals('terpinjam', \App\Models\PeminjamanAlat::latest('id')->first()->status);
    }

    public function test_api_return_peminjaman_restores_stock()
    {
        $admin = $this->adminToken();
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $lab = Laboratorium::factory()->create();
        $alat = Alat::factory()->create([
            'tipe_pelacakan' => 'agregat',
            'id_kategori' => $kategori->id,
            'id_labor' => $lab->id,
        ]);
        PengadaanAlat::factory()->create(['id_alat' => $alat->id, 'jumlah' => 10, 'tanggal_masuk' => now()]);

        $create = $this->postJson('/api/peminjaman', [
            'id_alat' => $alat->id,
            'keperluan' => 'Praktikum',
            'waktu_peminjaman' => now()->format('Y-m-d H:i'),
            'kondisi_saat_peminjaman' => 'baik',
            'jumlah' => 3,
        ]);

        $id = $create->json('data.id');

        $this->postJson("/api/peminjaman/{$id}/return", [
            'waktu_kembali_aktual' => now()->format('Y-m-d H:i'),
            'kondisi_saat_pengembalian' => 'baik',
        ])->assertStatus(200);

        $this->assertEquals(10, $alat->fresh()->getAvailableQuantity());
    }

    public function test_api_store_pemakaian_bahan_consumes_fifo_stock()
    {
        $this->adminToken();
        $kategori = Kategori::factory()->create(['jenis' => 'bahan']);
        $lab = Laboratorium::factory()->create();
        $bahan = Bahan::factory()->create([
            'id_kategori' => $kategori->id,
            'id_labor' => $lab->id,
        ]);
        $pengadaan = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 20,
            'stok_tersisa_batch' => 20,
            'tanggal_masuk' => now(),
        ]);

        $response = $this->postJson('/api/pemakaian-bahan', [
            'id_bahan' => $bahan->id,
            'id_pengadaan_bahan' => $pengadaan->id,
            'keperluan' => 'Rutin',
            'jumlah_pengambilan' => 15,
            'jumlah_terpakai' => 15,
            'waktu_pemakaian' => now()->format('Y-m-d H:i'),
        ]);

        $response->assertStatus(201);
        $this->assertEquals(5, $bahan->fresh()->getTotalStock());
        $this->assertEquals(5, $pengadaan->fresh()->stok_tersisa_batch);
    }

    public function test_api_mark_received_pengadaan_bahan_adds_stock()
    {
        $this->adminToken();
        $kategori = Kategori::factory()->create(['jenis' => 'bahan']);
        $lab = Laboratorium::factory()->create();
        $bahan = Bahan::factory()->create([
            'id_kategori' => $kategori->id,
            'id_labor' => $lab->id,
        ]);
        $pengadaan = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 20,
            'stok_tersisa_batch' => 0,
            'tanggal_masuk' => null,
        ]);

        $this->postJson("/api/pengadaan-bahan/{$pengadaan->id}/mark-received", [
            'tanggal_masuk' => now()->format('Y-m-d'),
        ])->assertStatus(200);

        $this->assertEquals(20, $bahan->fresh()->getTotalStock());
        $this->assertEquals(20, $pengadaan->fresh()->stok_tersisa_batch);
    }

    public function test_api_laboratorium_index_denied_for_mahasiswa()
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        Sanctum::actingAs($mahasiswa);

        $this->getJson('/api/laboratorium')->assertStatus(403);
    }

    public function test_api_users_index_denied_for_kepala_labor()
    {
        $kalab = User::factory()->create(['role' => 'kepala_labor']);
        Sanctum::actingAs($kalab);

        $this->getJson('/api/users')->assertStatus(403);
    }

    public function test_api_laporan_show_returns_ok()
    {
        $this->adminToken();
        \App\Models\PeminjamanAlat::factory()->create();

        $this->getJson('/api/laporan/peminjaman')->assertOk();
        $this->getJson('/api/dashboard')->assertOk();
    }
}