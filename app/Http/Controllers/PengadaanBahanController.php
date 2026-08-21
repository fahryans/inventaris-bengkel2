<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengadaanBahanRequest;
use App\Models\Bahan;
use App\Models\PengadaanBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class PengadaanBahanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PengadaanBahan::class);

        $query = PengadaanBahan::with(['bahan', 'userInput'])->latest();

        if ($request->filled('bahan')) {
            $query->where('id_bahan', $request->bahan);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
        }

        if ($request->filled('search')) {
            $query->whereHas('bahan', function ($q) use ($request) {
                $q->where('nama_bahan', 'like', '%' . $request->search . '%');
            });
        }

        $pengadaans = $query->paginate(15);
        $bahans = Bahan::all();

        return view('pengadaan_bahan.index', compact('pengadaans', 'bahans'));
    }

    public function create()
    {
        $this->authorize('create', PengadaanBahan::class);

        $bahans = Bahan::all();

        return view('pengadaan_bahan.create', compact('bahans'));
    }

    public function store(PengadaanBahanRequest $request)
    {
        $this->authorize('create', PengadaanBahan::class);

        $validated = $request->validated();
        $validated['id_user_input'] = Auth::id();

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        $pengadaan = PengadaanBahan::create($validated);

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['attributes' => $pengadaan->toArray()])
            ->event('created')
            ->log('Pengadaan bahan baru dicatat');

        return redirect()->route('pengadaan_bahan.index')
            ->with('success', 'Pengadaan bahan berhasil dicatat');
    }

    public function show($id)
    {
        $pengadaan = PengadaanBahan::findOrFail($id);
        $this->authorize('view', $pengadaan);

        $pengadaan->load(['bahan', 'userInput', 'pemakaianBahan']);

        return view('pengadaan_bahan.show', compact('pengadaan'));
    }

    public function edit($id)
    {
        $pengadaan = PengadaanBahan::findOrFail($id);
        $this->authorize('update', $pengadaan);

        $bahans = Bahan::all();

        return view('pengadaan_bahan.edit', compact('pengadaan', 'bahans'));
    }

    public function update(PengadaanBahanRequest $request, $id)
    {
        $pengadaan = PengadaanBahan::findOrFail($id);
        $this->authorize('update', $pengadaan);

        $oldData = $pengadaan->toArray();
        $validated = $request->validated();

        if ($request->hasFile('foto_transaksi')) {
            if ($pengadaan->foto_transaksi) {
                \Storage::disk('public')->delete($pengadaan->foto_transaksi);
            }
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        if ($pengadaan->tanggal_masuk && (int) $validated['jumlah'] < $pengadaan->jumlah - $pengadaan->stok_tersisa_batch) {
            return back()->withErrors([
                'jumlah' => 'Jumlah tidak boleh kurang dari yang sudah terpakai (' . ($pengadaan->jumlah - $pengadaan->stok_tersisa_batch) . ')',
            ]);
        }

        DB::transaction(function () use ($pengadaan, $validated) {
            if ($pengadaan->tanggal_masuk) {
                $oldJumlah = $pengadaan->jumlah;
                $oldStok = $pengadaan->stok_tersisa_batch;
                $used = $oldJumlah - $oldStok;
                $newJumlah = (int) $validated['jumlah'];
                $newStok = $newJumlah - $used;

                $validated['stok_tersisa_batch'] = $newStok;
            }

            unset($validated['tanggal_masuk']);

            $pengadaan->update($validated);
        });

        $pengadaan->refresh();

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['old' => $oldData, 'attributes' => $pengadaan->toArray()])
            ->event('updated')
            ->log('Pengadaan bahan diperbarui');

        return redirect()->route('pengadaan_bahan.show', $pengadaan)
            ->with('success', 'Pengadaan bahan berhasil diperbarui');
    }

    public function markReceived(Request $request, $id)
    {
        $pengadaan = PengadaanBahan::findOrFail($id);
        $this->authorize('update', $pengadaan);

        if ($pengadaan->tanggal_masuk) {
            return redirect()->route('pengadaan_bahan.show', $pengadaan)
                ->with('error', 'Pengadaan ini sudah pernah diterima');
        }

        $request->validate([
            'tanggal_masuk' => ['required', 'date'],
        ]);

        $oldData = $pengadaan->toArray();

        DB::transaction(function () use ($pengadaan, $request) {
            $pengadaan->update([
                'tanggal_masuk' => $request->tanggal_masuk,
                'stok_tersisa_batch' => $pengadaan->jumlah,
            ]);
        });

        $pengadaan->refresh();

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['old' => $oldData, 'attributes' => $pengadaan->toArray()])
            ->event('received')
            ->log('Bahan berhasil diterima');

        return redirect()->route('pengadaan_bahan.show', $pengadaan)
            ->with('success', 'Bahan berhasil diterima dan stok diperbarui');
    }

    public function destroy($id)
    {
        $pengadaan = PengadaanBahan::findOrFail($id);
        $this->authorize('delete', $pengadaan);

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['attributes' => $pengadaan->toArray()])
            ->event('deleted')
            ->log('Pengadaan bahan dihapus');

        DB::transaction(function () use ($pengadaan) {
            $pengadaan->delete();
        });

        return redirect()->route('pengadaan_bahan.index')
            ->with('success', 'Pengadaan bahan berhasil dihapus');
    }
}
