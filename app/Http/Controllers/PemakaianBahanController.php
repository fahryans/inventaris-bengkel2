<?php

namespace App\Http\Controllers;

use App\Http\Requests\PemakaianBahanRequest;
use App\Models\Bahan;
use App\Models\PemakaianBahan;
use App\Models\PengadaanBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PemakaianBahanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PemakaianBahan::class);

        $query = PemakaianBahan::with(['bahan', 'pengadaanBahan', 'userPemakai', 'userVerifikasi'])->latest();

        if ($request->filled('bahan')) {
            $query->where('id_bahan', $request->bahan);
        }

        if ($request->filled('verified')) {
            if ($request->verified === 'yes') {
                $query->whereNotNull('id_user_verifikasi');
            } else {
                $query->whereNull('id_user_verifikasi');
            }
        }

        if ($request->filled('search')) {
            $query->where('keperluan', 'like', '%' . $request->search . '%');
        }

        $pemakaians = $query->paginate(15);
        $bahans = Bahan::all();

        return view('pemakaian_bahan.index', compact('pemakaians', 'bahans'));
    }

    public function create()
    {
        $this->authorize('create', PemakaianBahan::class);

        $bahans = Bahan::all();
        $pengadaans = PengadaanBahan::all();

        return view('pemakaian_bahan.create', compact('bahans', 'pengadaans'));
    }

    public function store(PemakaianBahanRequest $request)
    {
        $this->authorize('create', PemakaianBahan::class);

        $validated = $request->validated();
        $validated['id_user_pemakai'] = Auth::id();

        PemakaianBahan::create($validated);

        $bahan = Bahan::find($validated['id_bahan']);
        $bahan->decrement('stok_saat_ini', $validated['jumlah_terpakai']);

        return redirect()->route('pemakaian_bahan.index')
            ->with('success', 'Pemakaian bahan berhasil dicatat');
    }

    public function show($id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('view', $pemakaian);

        $pemakaian->load(['bahan', 'pengadaanBahan', 'userPemakai', 'userVerifikasi']);

        return view('pemakaian_bahan.show', compact('pemakaian'));
    }

    public function edit($id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('update', $pemakaian);

        $bahans = Bahan::all();
        $pengadaans = PengadaanBahan::all();

        return view('pemakaian_bahan.edit', compact('pemakaian', 'bahans', 'pengadaans'));
    }

    public function update(PemakaianBahanRequest $request, $id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('update', $pemakaian);

        $oldJumlah = $pemakaian->jumlah_terpakai;
        $validated = $request->validated();

        $pemakaian->update($validated);

        $bahan = Bahan::find($validated['id_bahan']);
        $selisih = $validated['jumlah_terpakai'] - $oldJumlah;
        $bahan->decrement('stok_saat_ini', $selisih);

        return redirect()->route('pemakaian_bahan.show', $pemakaian)
            ->with('success', 'Pemakaian bahan berhasil diperbarui');
    }

    public function verify(Request $request, $id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('verify', $pemakaian);

        $pemakaian->update([
            'id_user_verifikasi' => Auth::id(),
        ]);

        return redirect()->route('pemakaian_bahan.show', $pemakaian)
            ->with('success', 'Pemakaian bahan berhasil diverifikasi');
    }

    public function destroy($id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('delete', $pemakaian);

        $bahan = $pemakaian->bahan;
        $bahan->increment('stok_saat_ini', $pemakaian->jumlah_terpakai);

        $pemakaian->delete();

        return redirect()->route('pemakaian_bahan.index')
            ->with('success', 'Pemakaian bahan berhasil dihapus');
    }
}
