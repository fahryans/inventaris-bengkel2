<?php

namespace App\Http\Controllers;

use App\Http\Requests\PemakaianBahanRequest;
use App\Models\Bahan;
use App\Models\PemakaianBahan;
use App\Models\PengadaanBahan;
use App\Services\FIFOService;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class PemakaianBahanController extends Controller
{
    public function __construct(
        protected FIFOService $fifoService,
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
        $this->authorize('viewAny', PemakaianBahan::class);

        $user = Auth::user();
        $query = PemakaianBahan::with(['bahan', 'pengadaanBahan', 'userPemakai', 'userVerifikasi'])->latest();

        $labIds = $this->getLabIds();
        if ($labIds) {
            $query->whereHas('bahan', fn($q) => $q->whereIn('id_labor', $labIds));
        }

        if ($request->filled('bahan')) {
            $query->where('id_bahan', $request->bahan);
        }

        if ($request->has('verified') && $request->verified !== '') {
            if (in_array($request->verified, ['yes', '1', 1, true], true)) {
                $query->whereNotNull('id_user_verifikasi');
            } else {
                $query->whereNull('id_user_verifikasi');
            }
        }

        if ($request->filled('search')) {
            $query->where('keperluan', 'like', '%' . $request->search . '%');
        }

        $pemakaians = $query->paginate(15);
        $bahans = $labIds ? Bahan::whereIn('id_labor', $labIds)->get() : Bahan::all();

        return view('pemakaian_bahan.index', compact('pemakaians', 'bahans'));
    }

    public function create()
    {
        $this->authorize('create', PemakaianBahan::class);

        $labIds = $this->getLabIds();
        $bahans = $labIds ? Bahan::whereIn('id_labor', $labIds)->get() : Bahan::all();
        $pengadaans = $labIds ? PengadaanBahan::whereHas('bahan', fn($q) => $q->whereIn('id_labor', $labIds))->get() : PengadaanBahan::all();

        return view('pemakaian_bahan.create', compact('bahans', 'pengadaans'));
    }

    public function store(PemakaianBahanRequest $request)
    {
        $this->authorize('create', PemakaianBahan::class);

        $validated = $request->validated();
        $validated['id_user_pemakai'] = Auth::id();

        // Auto-set lab from bahan's lab
        if (empty($validated['id_laboratorium']) && !empty($validated['id_bahan'])) {
            $bahan = Bahan::find($validated['id_bahan']);
            if ($bahan) {
                $validated['id_laboratorium'] = $bahan->id_labor;
            }
        }

        $pemakaian = null;

        DB::transaction(function () use ($validated, &$pemakaian) {
            $pemakaian = PemakaianBahan::create($validated);

            // Stok langsung berkurang sesuai jumlah pengambilan
            $this->fifoService->consumeFromBatches(
                $validated['id_bahan'],
                $validated['jumlah_pengambilan']
            );
        });

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['attributes' => $pemakaian->toArray()])
            ->event('created')
            ->log('Pemakaian bahan baru dicatat');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Pemakaian bahan berhasil dicatat']);
        }

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

        $labIds = $this->getLabIds();
        $bahans = $labIds ? Bahan::whereIn('id_labor', $labIds)->get() : Bahan::all();
        $pengadaans = $labIds ? PengadaanBahan::whereHas('bahan', fn($q) => $q->whereIn('id_labor', $labIds))->get() : PengadaanBahan::all();

        return view('pemakaian_bahan.edit', compact('pemakaian', 'bahans', 'pengadaans'));
    }

    public function update(PemakaianBahanRequest $request, $id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('update', $pemakaian);

        $oldJumlah = $pemakaian->jumlah_terpakai;
        $oldBahanId = $pemakaian->id_bahan;
        $oldData = $pemakaian->toArray();
        $validated = $request->validated();
        $newJumlah = $validated['jumlah_terpakai'];
        $newBahanId = $validated['id_bahan'];

        DB::transaction(function () use ($pemakaian, $validated, $oldJumlah, $oldBahanId, $newJumlah, $newBahanId) {
            $pemakaian->update($validated);

            if ($oldBahanId === $newBahanId) {
                $selisih = $newJumlah - $oldJumlah;
                if ($selisih > 0) {
                    $this->fifoService->consumeFromBatches($newBahanId, $selisih);
                } elseif ($selisih < 0) {
                    $this->fifoService->reverseConsumeFromBatches($newBahanId, abs($selisih));
                }
            } else {
                $this->fifoService->reverseConsumeFromBatches($oldBahanId, $oldJumlah);
                $this->fifoService->consumeFromBatches($newBahanId, $newJumlah);
            }
        });

        $pemakaian->refresh();

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
            ->event('updated')
            ->log('Pemakaian bahan diperbarui');

        return redirect()->route('pemakaian_bahan.show', $pemakaian)
            ->with('success', 'Pemakaian bahan berhasil diperbarui');
    }

    public function verify(Request $request, $id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('verify', $pemakaian);

        $oldData = $pemakaian->toArray();

        $pemakaian->update([
            'id_user_verifikasi' => Auth::id(),
        ]);

        $pemakaian->refresh();

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
            ->event('verified')
            ->log('Pemakaian bahan berhasil diverifikasi');

        return redirect()->route('pemakaian_bahan.show', $pemakaian)
            ->with('success', 'Pemakaian bahan berhasil diverifikasi');
    }

    public function destroy($id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('delete', $pemakaian);

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['attributes' => $pemakaian->toArray()])
            ->event('deleted')
            ->log('Pemakaian bahan dihapus');

        DB::transaction(function () use ($pemakaian) {
            $this->fifoService->reverseConsumeFromBatches(
                $pemakaian->id_bahan,
                $pemakaian->jumlah_terpakai
            );

            $pemakaian->delete();
        });

        return redirect()->route('pemakaian_bahan.index')
            ->with('success', 'Pemakaian bahan berhasil dihapus');
    }

    public function returnBahan(Request $request, $id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('return', $pemakaian);

        $validated = $request->validate([
            'jumlah_terpakai' => ['required', 'integer', 'min:1', 'lte:' . $pemakaian->jumlah_pengambilan],
            'jumlah_pengembalian' => ['required', 'integer', 'min:0'],
        ]);

        $sisa = $pemakaian->jumlah_pengambilan - $validated['jumlah_terpakai'];

        if ($validated['jumlah_pengembalian'] > $sisa) {
            return back()->withErrors(['jumlah_pengembalian' => 'Jumlah pengembalian melebihi sisa (maksimal ' . $sisa . ')']);
        }

        if ($validated['jumlah_pengembalian'] < 0) {
            return back()->withErrors(['jumlah_pengembalian' => 'Jumlah pengembalian tidak boleh negatif']);
        }

        $oldData = $pemakaian->toArray();
        $isKadep = Auth::user()->role === 'kadep';

        DB::transaction(function () use ($pemakaian, $validated, $isKadep) {
            $pemakaian->update([
                'jumlah_terpakai' => $validated['jumlah_terpakai'],
                'jumlah_pengembalian' => $validated['jumlah_pengembalian'],
                'status_pengembalian' => $isKadep ? 'verified' : 'pending',
                'waktu_pengembalian' => now(),
            ]);

            if ($isKadep && $validated['jumlah_pengembalian'] > 0) {
                $this->fifoService->reverseConsumeFromBatches(
                    $pemakaian->id_bahan,
                    $validated['jumlah_pengembalian']
                );
            }
        });

        $pemakaian->refresh();

        $event = $isKadep ? 'returned' : 'returned_pending';
        $logMessage = $isKadep
            ? 'Pengembalian sisa bahan dicatat dan stok dikembalikan'
            : 'Pengembalian sisa bahan dicatat, menunggu verifikasi';

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
            ->event($event)
            ->log($logMessage);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => $logMessage]);
        }

        return redirect()->route('pemakaian_bahan.show', $pemakaian)
            ->with('success', $logMessage);
    }

    public function verifyReturn(Request $request, $id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('verifyReturn', $pemakaian);

        $oldData = $pemakaian->toArray();

        DB::transaction(function () use ($pemakaian) {
            $pemakaian->update([
                'status_pengembalian' => 'verified',
            ]);

            if ($pemakaian->jumlah_pengembalian > 0) {
                $this->fifoService->reverseConsumeFromBatches(
                    $pemakaian->id_bahan,
                    $pemakaian->jumlah_pengembalian
                );
            }
        });

        $pemakaian->refresh();

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
            ->event('verified')
            ->log('Pengembalian bahan diverifikasi dan stok dikembalikan');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Pengembalian bahan berhasil diverifikasi']);
        }

        return redirect()->route('pemakaian_bahan.show', $pemakaian)
            ->with('success', 'Pengembalian bahan berhasil diverifikasi');
    }

    public function rejectReturn(Request $request, $id)
    {
        $pemakaian = PemakaianBahan::findOrFail($id);
        $this->authorize('rejectReturn', $pemakaian);

        $oldData = $pemakaian->toArray();

        $pemakaian->update([
            'status_pengembalian' => 'rejected',
        ]);

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
            ->event('rejected')
            ->log('Pengembalian bahan ditolak');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Pengembalian bahan ditolak']);
        }

        return redirect()->route('pemakaian_bahan.show', $pemakaian)
            ->with('success', 'Pengembalian bahan ditolak');
    }
}
