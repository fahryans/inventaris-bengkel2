<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengadaanAlatRequest;
use App\Models\Alat;
use App\Models\PengadaanAlat;
use App\Models\UnitAlat;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class PengadaanAlatController extends Controller
{
    public function __construct(
        protected StokService $stokService,
    ) {}

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
        $this->authorize('viewAny', PengadaanAlat::class);

        $user = Auth::user();
        $query = PengadaanAlat::with(['alat', 'spesifikasiAlat', 'userInput'])->latest();

        $labIds = $this->getLabIds();
        if ($labIds) {
            $query->whereHas('alat', fn($q) => $q->whereIn('id_labor', $labIds));
        }

        if ($request->filled('alat')) {
            $query->where('id_alat', $request->alat);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
        }

        if ($request->filled('search')) {
            $query->whereHas('alat', function ($q) use ($request) {
                $q->where('nama_alat', 'like', '%' . $request->search . '%');
            });
        }

        $pengadaans = $query->paginate(15);
        $alatsQuery = Alat::with('spesifikasiAlat');
        if ($labIds) {
            $alatsQuery->whereIn('id_labor', $labIds);
        }
        $alats = $alatsQuery->get();

        return view('pengadaan_alat.index', compact('pengadaans', 'alats'));
    }

    public function create()
    {
        $this->authorize('create', PengadaanAlat::class);

        $labIds = $this->getLabIds();
        $alatsQuery = Alat::with('spesifikasiAlat');
        if ($labIds) {
            $alatsQuery->whereIn('id_labor', $labIds);
        }
        $alats = $alatsQuery->get();

        return view('pengadaan_alat.create', compact('alats'));
    }

    public function store(PengadaanAlatRequest $request)
    {
        $this->authorize('create', PengadaanAlat::class);

        $validated = $request->validated();
        $validated['id_user_input'] = Auth::id();

        // Validasi manual: kode_inventaris required untuk agregat
        $alat = Alat::find($validated['id_alat']);
        if ($alat && $alat->tipe_pelacakan === 'agregat' && empty($validated['kode_inventaris'])) {
            return back()->withErrors(['kode_inventaris' => 'Kode inventaris wajib diisi untuk alat agregat'])
                ->withInput();
        }

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        DB::transaction(function () use ($validated, $alat, &$pengadaan) {
            $pengadaan = PengadaanAlat::create($validated);

            // Auto-create unit alat records for tipe unit
            if ($alat && $alat->tipe_pelacakan === 'unit') {
                for ($i = 1; $i <= $pengadaan->jumlah; $i++) {
                    UnitAlat::create([
                        'id_alat' => $pengadaan->id_alat,
                        'id_spesifikasi_alat' => $pengadaan->id_spesifikasi_alat,
                        'kode_inventaris' => null,
                        'kondisi_saat_ini' => 'baik',
                        'status' => 'tersedia',
                    ]);
                }
            }
        });

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['attributes' => $pengadaan->toArray()])
            ->event('created')
            ->log('Pengadaan alat baru dicatat');

        return redirect()->route('pengadaan_alat.index')
            ->with('success', 'Pengadaan alat berhasil dicatat');
    }

    public function show($id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('view', $pengadaan);

        $pengadaan->load(['alat', 'spesifikasiAlat', 'userInput']);

        return view('pengadaan_alat.show', compact('pengadaan'));
    }

    public function edit($id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('update', $pengadaan);

        $pengadaan->load('alat');
        $labIds = $this->getLabIds();
        $alatsQuery = Alat::with('spesifikasiAlat');
        if ($labIds) {
            $alatsQuery->whereIn('id_labor', $labIds);
        }
        $alats = $alatsQuery->get();

        return view('pengadaan_alat.edit', compact('pengadaan', 'alats'));
    }

    public function update(PengadaanAlatRequest $request, $id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('update', $pengadaan);

        $oldData = $pengadaan->toArray();
        $validated = $request->validated();

        // Validasi manual: kode_inventaris required untuk agregat
        $alat = $pengadaan->alat;
        if ($alat && $alat->tipe_pelacakan === 'agregat' && empty($validated['kode_inventaris'])) {
            return back()->withErrors(['kode_inventaris' => 'Kode inventaris wajib diisi untuk alat agregat'])
                ->withInput();
        }

        if ($request->hasFile('foto_transaksi')) {
            if ($pengadaan->foto_transaksi) {
                \Storage::disk('public')->delete($pengadaan->foto_transaksi);
            }
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        $pengadaan->update($validated);

        $pengadaan->refresh();

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['old' => $oldData, 'attributes' => $pengadaan->toArray()])
            ->event('updated')
            ->log('Pengadaan alat diperbarui');

        return redirect()->route('pengadaan_alat.show', $pengadaan)
            ->with('success', 'Pengadaan alat berhasil diperbarui');
    }

    public function markReceived(Request $request, $id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('update', $pengadaan);

        if ($pengadaan->tanggal_masuk) {
            return redirect()->route('pengadaan_alat.show', $pengadaan)
                ->with('error', 'Pengadaan ini sudah pernah diterima');
        }

        $request->validate([
            'tanggal_masuk' => ['required', 'date'],
        ]);

        $oldData = $pengadaan->toArray();

        DB::transaction(function () use ($pengadaan, $request) {
            $pengadaan->update([
                'tanggal_masuk' => $request->tanggal_masuk,
            ]);

            if ($pengadaan->alat->isUnitTracked()) {
                // Unit alat already created on pengadaan store, just update status if needed
                $pengadaan->alat->unitAlat()
                    ->where('kode_inventaris', null)
                    ->update(['status' => 'tersedia']);
            } else {
                $this->stokService->tambahAlatAgregat(
                    $pengadaan->alat,
                    $pengadaan->jumlah
                );
            }
        });

        $pengadaan->refresh();

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['old' => $oldData, 'attributes' => $pengadaan->toArray()])
            ->event('received')
            ->log('Alat berhasil diterima');

        return redirect()->route('pengadaan_alat.show', $pengadaan)
            ->with('success', 'Alat berhasil diterima dan stok diperbarui');
    }

    public function destroy($id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('delete', $pengadaan);

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['attributes' => $pengadaan->toArray()])
            ->event('deleted')
            ->log('Pengadaan alat dihapus');

        // Hapus unit alat yang terkait jika tipe unit
        if ($pengadaan->alat && $pengadaan->alat->tipe_pelacakan === 'unit') {
            $pengadaan->alat->unitAlat()->where('kode_inventaris', null)->delete();
        }

        $pengadaan->delete();

        return redirect()->route('pengadaan_alat.index')
            ->with('success', 'Pengadaan alat berhasil dihapus');
    }
}