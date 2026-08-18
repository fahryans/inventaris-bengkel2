<?php

namespace App\Http\Controllers;

use App\Http\Requests\PemeliharaanAlatRequest;
use App\Models\PemeliharaanAlat;
use App\Models\UnitAlat;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;

class PemeliharaanAlatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PemeliharaanAlat::class);

        $query = PemeliharaanAlat::with(['unitAlat.alat', 'teknisi'])->latest();

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('tanggal_cek_berikutnya', '<', now());
            } elseif ($request->status === 'upcoming') {
                $query->whereBetween('tanggal_cek_berikutnya', [now(), now()->addDays(7)]);
            }
        }

        if ($request->filled('teknisi')) {
            $query->where('id_teknisi', $request->teknisi);
        }

        if ($request->filled('search')) {
            $query->whereHas('unitAlat', function ($q) use ($request) {
                $q->where('kode_inventaris', 'like', '%' . $request->search . '%');
            });
        }

        $pemeliharaans = $query->paginate(15);
        $teknisis = User::where('role', 'teknisi')->get();

        return view('pemeliharaan.index', compact('pemeliharaans', 'teknisis'));
    }

    public function create()
    {
        $this->authorize('create', PemeliharaanAlat::class);

        $unitAlats = UnitAlat::with('alat')->get();
        $teknisis = User::where('role', 'teknisi')->get();

        return view('pemeliharaan.create', compact('unitAlats', 'teknisis'));
    }

    public function store(PemeliharaanAlatRequest $request)
    {
        $this->authorize('create', PemeliharaanAlat::class);

        $pemeliharaan = PemeliharaanAlat::create($request->validated());

        activity()
            ->performedOn($pemeliharaan)
            ->withProperties(['attributes' => $pemeliharaan->toArray()])
            ->event('created')
            ->log('Pemeliharaan alat baru dijadwalkan');

        return redirect()->route('pemeliharaan.index')
            ->with('success', 'Pemeliharaan alat berhasil dijadwalkan');
    }

    public function show($id)
    {
        $pemeliharaan = PemeliharaanAlat::findOrFail($id);
        $this->authorize('view', $pemeliharaan);

        $pemeliharaan->load(['unitAlat.alat', 'teknisi']);

        return view('pemeliharaan.show', compact('pemeliharaan'));
    }

    public function edit($id)
    {
        $pemeliharaan = PemeliharaanAlat::findOrFail($id);
        $this->authorize('update', $pemeliharaan);

        $unitAlats = UnitAlat::with('alat')->get();
        $teknisis = User::where('role', 'teknisi')->get();

        return view('pemeliharaan.edit', compact('pemeliharaan', 'unitAlats', 'teknisis'));
    }

    public function update(PemeliharaanAlatRequest $request, $id)
    {
        $pemeliharaan = PemeliharaanAlat::findOrFail($id);
        $this->authorize('update', $pemeliharaan);

        $oldData = $pemeliharaan->toArray();
        $pemeliharaan->update($request->validated());

        activity()
            ->performedOn($pemeliharaan)
            ->withProperties(['old' => $oldData, 'attributes' => $pemeliharaan->toArray()])
            ->event('updated')
            ->log('Pemeliharaan alat diperbarui');

        return redirect()->route('pemeliharaan.show', $pemeliharaan)
            ->with('success', 'Pemeliharaan alat berhasil diperbarui');
    }

    public function complete(Request $request, $id)
    {
        $pemeliharaan = PemeliharaanAlat::findOrFail($id);
        $this->authorize('complete', $pemeliharaan);

        $request->validate([
            'kondisi' => ['required', 'string', 'max:255'],
            'hasil_pemeliharaan' => ['nullable', 'string'],
        ]);

        $oldData = $pemeliharaan->toArray();

        $pemeliharaan->update([
            'kondisi' => $request->kondisi,
            'hasil_pemeliharaan' => $request->hasil_pemeliharaan,
            'tanggal_cek' => now(),
        ]);

        $pemeliharaan->unitAlat->update(['kondisi_saat_ini' => $request->kondisi]);

        $pemeliharaan->refresh();

        activity()
            ->performedOn($pemeliharaan)
            ->withProperties(['old' => $oldData, 'attributes' => $pemeliharaan->toArray()])
            ->event('completed')
            ->log('Pemeliharaan alat selesai');

        return redirect()->route('pemeliharaan.show', $pemeliharaan)
            ->with('success', 'Pemeliharaan alat berhasil diselesaikan');
    }

    public function destroy($id)
    {
        $pemeliharaan = PemeliharaanAlat::findOrFail($id);
        $this->authorize('delete', $pemeliharaan);

        activity()
            ->performedOn($pemeliharaan)
            ->withProperties(['attributes' => $pemeliharaan->toArray()])
            ->event('deleted')
            ->log('Pemeliharaan alat dihapus');

        $pemeliharaan->delete();

        return redirect()->route('pemeliharaan.index')
            ->with('success', 'Pemeliharaan alat berhasil dihapus');
    }
}
