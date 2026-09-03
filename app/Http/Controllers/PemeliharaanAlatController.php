<?php

namespace App\Http\Controllers;

use App\Http\Requests\PemeliharaanAlatRequest;
use App\Models\PemeliharaanAlat;
use App\Models\UnitAlat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

class PemeliharaanAlatController extends Controller
{
    private function getLabIds()
    {
        $user = Auth::user();
        if ($user->role === 'teknisi') {
            return $user->laboratoriumTeknisi->pluck('id')->toArray();
        } elseif ($user->role === 'kepala_labor') {
            return $user->laboratoriumDikelola->pluck('id')->toArray();
        }
        return null;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', PemeliharaanAlat::class);

        $user = Auth::user();
        $query = PemeliharaanAlat::with(['unitAlat.alat', 'teknisi'])->latest();

        $labIds = $this->getLabIds();
        if ($labIds) {
            $query->whereHas('unitAlat.alat', fn($q) => $q->whereIn('id_labor', $labIds));
        }

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                // Hanya jadwal TERAKHIR per unit yang dianggap overdue;
                // jadwal lama yang sudah digantikan tidak lagi merah.
                $query->whereIn('id', PemeliharaanAlat::selectRaw('MAX(id) as id')->groupBy('id_unit_alat'))
                    ->where('tanggal_cek_berikutnya', '<', now());
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

        // Id pemeliharaan TERAKHIR per unit alat: acuan status overdue.
        $latestIds = PemeliharaanAlat::selectRaw('MAX(id) as id')
            ->groupBy('id_unit_alat')
            ->pluck('id')
            ->map(fn($i) => (int) $i)
            ->toArray();

        return view('pemeliharaan.index', compact('pemeliharaans', 'teknisis', 'latestIds'));
    }

    public function create()
    {
        $this->authorize('create', PemeliharaanAlat::class);

        $user = Auth::user();
        $labIds = $this->getLabIds();
        $unitAlatsQuery = UnitAlat::with('alat');
        if ($labIds) {
            $unitAlatsQuery->whereHas('alat', fn($q) => $q->whereIn('id_labor', $labIds));
        }
        $unitAlats = $unitAlatsQuery->get();

        if ($user->role === 'teknisi') {
            $teknisis = collect([$user]);
        } elseif ($user->role === 'kepala_labor' && $labIds) {
            $teknisis = User::where('role', 'teknisi')
                ->whereHas('laboratoriumTeknisi', fn($q) => $q->whereIn('laboratorium.id', $labIds))
                ->get();
        } else {
            $teknisis = User::where('role', 'teknisi')->get();
        }

        return view('pemeliharaan.create', compact('unitAlats', 'teknisis'));
    }

    public function store(PemeliharaanAlatRequest $request)
    {
        $this->authorize('create', PemeliharaanAlat::class);

        $validated = $request->validated();
        $user = Auth::user();

        if ($user->role === 'teknisi') {
            $validated['id_teknisi'] = $user->id;

            $unit = UnitAlat::with('alat')->find($validated['id_unit_alat']);
            if (!$unit || !$user->isTeknisiOf($unit->alat->id_labor)) {
                abort(403, 'Unit alat di luar laboratorium Anda');
            }
        } elseif ($user->role === 'kepala_labor') {
            $labIds = $this->getLabIds();
            $teknisiDapat = User::where('role', 'teknisi')
                ->where('id', $validated['id_teknisi'])
                ->whereHas('laboratoriumTeknisi', fn($q) => $q->whereIn('laboratorium.id', $labIds))
                ->exists();
            if (!$teknisiDapat) {
                abort(403, 'Teknisi di luar laboratorium Anda');
            }
        }

        $pemeliharaan = PemeliharaanAlat::create($validated);

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
        $pemeliharaan = PemeliharaanAlat::with('unitAlat.alat')->findOrFail($id);
        $this->authorize('update', $pemeliharaan);

        $labIds = $this->getLabIds();
        $unitAlatsQuery = UnitAlat::with('alat');
        if ($labIds) {
            $unitAlatsQuery->whereHas('alat', fn($q) => $q->whereIn('id_labor', $labIds));
        }
        $unitAlats = $unitAlatsQuery->get();
        $teknisis = User::where('role', 'teknisi')->get();

        return view('pemeliharaan.edit', compact('pemeliharaan', 'unitAlats', 'teknisis'));
    }

    public function update(PemeliharaanAlatRequest $request, $id)
    {
        $pemeliharaan = PemeliharaanAlat::with('unitAlat.alat')->findOrFail($id);
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
        $pemeliharaan = PemeliharaanAlat::with('unitAlat.alat')->findOrFail($id);
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
        $pemeliharaan = PemeliharaanAlat::with('unitAlat.alat')->findOrFail($id);
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
