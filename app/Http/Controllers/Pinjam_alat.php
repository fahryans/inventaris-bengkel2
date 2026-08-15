<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\PeminjamanAlat;
use App\Models\UnitAlat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Pinjam_alat extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', PeminjamanAlat::class);

        $peminjaman = PeminjamanAlat::with(['alat', 'unitAlat', 'userPeminjam'])
            ->latest('waktu_peminjaman')
            ->paginate(15);

        return view('peminjaman.index', compact('peminjaman'));
    }

    public function create()
    {
        $this->authorize('create', PeminjamanAlat::class);

        $alats = Alat::where('tipe_pelacakan', 'agregat')->get();
        $units = UnitAlat::with('alat')->get();

        return view('peminjaman.create', compact('alats', 'units'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PeminjamanAlat::class);

        $validated = $request->validate([
            'id_alat' => ['nullable', 'exists:alat,id'],
            'id_unit_alat' => ['nullable', 'exists:unit_alat,id'],
            'keperluan' => ['required', 'string', 'max:255'],
            'waktu_peminjaman' => ['required', 'date_format:Y-m-d H:i'],
            'waktu_pengembalian' => ['nullable', 'date_format:Y-m-d H:i', 'after:waktu_peminjaman'],
            'jumlah' => ['nullable', 'integer', 'min:1'],
            'kondisi_saat_peminjaman' => ['required', 'string', 'max:255'],
        ]);

        if (!$validated['id_alat'] && !$validated['id_unit_alat']) {
            throw ValidationException::withMessages([
                'id_alat' => 'Harus memilih salah satu: alat atau unit alat',
            ]);
        }

        $validated['id_user_peminjam'] = Auth::id();
        $validated['jumlah'] = $validated['jumlah'] ?? 1;
        $validated['status'] = 'terpinjam';

        PeminjamanAlat::create($validated);

        return redirect()->route('peminjaman.index')
            ->with('success', 'Peminjaman berhasil dibuat');
    }

    public function show(PeminjamanAlat $peminjaman)
    {
        $this->authorize('view', $peminjaman);

        $peminjaman->load(['alat', 'unitAlat', 'userPeminjam']);

        return view('peminjaman.show', compact('peminjaman'));
    }

    public function edit(PeminjamanAlat $peminjaman)
    {
        $this->authorize('update', $peminjaman);

        if ($peminjaman->status === 'sudah_dikembalikan') {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Tidak dapat mengedit peminjaman yang sudah dikembalikan');
        }

        $alats = Alat::where('tipe_pelacakan', 'agregat')->get();
        $units = UnitAlat::with('alat')->get();

        return view('peminjaman.edit', compact('peminjaman', 'alats', 'units'));
    }

    public function update(Request $request, PeminjamanAlat $peminjaman)
    {
        $this->authorize('update', $peminjaman);

        if ($peminjaman->status === 'sudah_dikembalikan') {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Tidak dapat mengedit peminjaman yang sudah dikembalikan');
        }

        $validated = $request->validate([
            'keperluan' => ['required', 'string', 'max:255'],
            'waktu_pengembalian' => ['nullable', 'date_format:Y-m-d H:i', 'after:waktu_peminjaman'],
            'kondisi_saat_peminjaman' => ['required', 'string', 'max:255'],
        ]);

        $peminjaman->update($validated);

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('success', 'Peminjaman berhasil diperbarui');
    }

    public function return(Request $request, PeminjamanAlat $peminjaman)
    {
        $this->authorize('return', $peminjaman);

        if ($peminjaman->status === 'sudah_dikembalikan') {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Peminjaman sudah dikembalikan sebelumnya');
        }

        $validated = $request->validate([
            'waktu_kembali_aktual' => ['required', 'date_format:Y-m-d H:i'],
            'kondisi_saat_pengembalian' => ['required', 'string', 'max:255'],
        ]);

        $peminjaman->update([
            'waktu_kembali_aktual' => $validated['waktu_kembali_aktual'],
            'kondisi_saat_pengembalian' => $validated['kondisi_saat_pengembalian'],
            'status' => 'sudah_dikembalikan',
        ]);

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('success', 'Alat berhasil dikembalikan');
    }

    public function destroy(PeminjamanAlat $peminjaman)
    {
        $this->authorize('delete', $peminjaman);

        if ($peminjaman->status === 'terpinjam') {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Tidak dapat menghapus peminjaman yang masih aktif');
        }

        $peminjaman->delete();

        return redirect()->route('peminjaman.index')
            ->with('success', 'Peminjaman berhasil dihapus');
    }
}
