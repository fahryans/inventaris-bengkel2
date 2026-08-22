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

    public function index(Request $request)
    {
        $this->authorize('viewAny', PemakaianBahan::class);

        $query = PemakaianBahan::with(['bahan', 'pengadaanBahan', 'userPemakai', 'userVerifikasi'])->latest();

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

        $pemakaian = null;

        DB::transaction(function () use ($validated, &$pemakaian) {
            $pemakaian = PemakaianBahan::create($validated);

            // Hanya konsumsi stok jika jumlah_terpakai diisi dan > 0
            if ($validated['jumlah_terpakai'] ?? 0 > 0) {
                $this->fifoService->consumeFromBatches(
                    $validated['id_bahan'],
                    $validated['jumlah_terpakai']
                );
            }
        });

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['attributes' => $pemakaian->toArray()])
            ->event('created')
            ->log('Pemakaian bahan baru dicatat');

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

        DB::transaction(function () use ($pemakaian, $validated) {
            $pemakaian->update([
                'jumlah_terpakai' => $validated['jumlah_terpakai'],
                'jumlah_pengembalian' => $validated['jumlah_pengembalian'],
            ]);

            if ($validated['jumlah_pengembalian'] > 0) {
                $this->fifoService->reverseConsumeFromBatches(
                    $pemakaian->id_bahan,
                    $validated['jumlah_pengembalian']
                );
            }
        });

        $pemakaian->refresh();

        activity()
            ->performedOn($pemakaian)
            ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
            ->event('returned')
            ->log('Pengembalian sisa bahan dicatat');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Pengembalian sisa bahan berhasil dicatat']);
        }

        return redirect()->route('pemakaian_bahan.show', $pemakaian)
            ->with('success', 'Pengembalian sisa bahan berhasil dicatat');
    }
}
