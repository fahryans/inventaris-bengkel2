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

        DB::transaction(function () use ($validated) {
            PemakaianBahan::create($validated);

            $this->fifoService->consumeFromBatches(
                $validated['id_bahan'],
                $validated['jumlah_terpakai']
            );
        });

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
}
