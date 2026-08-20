<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\PeminjamanAlat;
use App\Models\UnitAlat;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

class PeminjamanAlatController extends Controller
{
    public function __construct(
        protected PeminjamanService $peminjamanService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PeminjamanAlat::class);

        $query = PeminjamanAlat::with(['alat', 'spesifikasiAlat', 'unitAlat', 'userPeminjam']);

        if (in_array(Auth::user()->role, ['dosen', 'mahasiswa'])) {
            $query->where('id_user_peminjam', Auth::id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('keperluan', 'like', '%' . $request->search . '%');
        }

        $peminjaman = $query->latest('waktu_peminjaman')->paginate(15);

        return view('peminjaman.index', compact('peminjaman'));
    }

    public function create()
    {
        $this->authorize('create', PeminjamanAlat::class);

        $alats = Alat::with('spesifikasiAlat')->where('tipe_pelacakan', 'agregat')->get();
        $units = UnitAlat::with('alat', 'spesifikasiAlat')->get();

        return view('peminjaman.create', compact('alats', 'units'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PeminjamanAlat::class);

        $validated = $request->validate([
            'id_alat' => ['nullable', 'exists:alat,id'],
            'id_unit_alat' => ['nullable', 'exists:unit_alat,id'],
            'id_spesifikasi_alat' => ['nullable', 'exists:spesifikasi_alat,id'],
            'keperluan' => ['required', 'string', 'max:255'],
            'waktu_peminjaman' => ['required', 'date_format:Y-m-d H:i'],
            'waktu_pengembalian' => ['nullable', 'date_format:Y-m-d H:i', 'after:waktu_peminjaman'],
            'jumlah' => ['nullable', 'integer', 'min:1'],
            'kondisi_saat_peminjaman' => ['required', 'string', 'max:255'],
        ]);

        $validated['id_user_peminjam'] = Auth::id();

        $peminjaman = $this->peminjamanService->createBorrowing($validated);

        activity()
            ->performedOn($peminjaman)
            ->withProperties(['attributes' => $peminjaman->toArray()])
            ->event('created')
            ->log('Peminjaman alat baru dibuat');

        return redirect()->route('peminjaman.index')
            ->with('success', 'Peminjaman berhasil dibuat');
    }

    public function show(PeminjamanAlat $peminjaman)
    {
        $this->authorize('view', $peminjaman);

        $peminjaman->load(['alat', 'spesifikasiAlat', 'unitAlat', 'userPeminjam']);

        return view('peminjaman.show', compact('peminjaman'));
    }

    public function edit(PeminjamanAlat $peminjaman)
    {
        $this->authorize('update', $peminjaman);

        if ($peminjaman->status === 'sudah_dikembalikan') {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Tidak dapat mengedit peminjaman yang sudah dikembalikan');
        }

        $alats = Alat::with('spesifikasiAlat')->where('tipe_pelacakan', 'agregat')->get();
        $units = UnitAlat::with('alat', 'spesifikasiAlat')->get();

        return view('peminjaman.edit', compact('peminjaman', 'alats', 'units'));
    }

    public function update(Request $request, PeminjamanAlat $peminjaman)
    {
        $this->authorize('update', $peminjaman);

        if ($peminjaman->status === 'sudah_dikembalikan') {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Tidak dapat mengedit peminjaman yang sudah dikembalikan');
        }

        $oldData = $peminjaman->toArray();

        $validated = $request->validate([
            'keperluan' => ['required', 'string', 'max:255'],
            'waktu_pengembalian' => ['nullable', 'date_format:Y-m-d H:i', 'after:waktu_peminjaman'],
            'kondisi_saat_peminjaman' => ['required', 'string', 'max:255'],
        ]);

        $peminjaman->update($validated);

        activity()
            ->performedOn($peminjaman)
            ->withProperties(['old' => $oldData, 'attributes' => $peminjaman->toArray()])
            ->event('updated')
            ->log('Peminjaman alat diperbarui');

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('success', 'Peminjaman berhasil diperbarui');
    }

    public function returnForm(PeminjamanAlat $peminjaman)
    {
        $this->authorize('return', $peminjaman);

        $peminjaman->load(['alat', 'spesifikasiAlat', 'unitAlat', 'userPeminjam']);

        return view('peminjaman.return', compact('peminjaman'));
    }

    public function return(Request $request, PeminjamanAlat $peminjaman)
    {
        $this->authorize('return', $peminjaman);

        $oldData = $peminjaman->toArray();

        $validated = $request->validate([
            'waktu_kembali_aktual' => ['required', 'date_format:Y-m-d H:i'],
            'kondisi_saat_pengembalian' => ['required', 'string', 'max:255'],
        ]);

        $this->peminjamanService->returnBorrowing($peminjaman, $validated);

        $peminjaman->refresh();

        activity()
            ->performedOn($peminjaman)
            ->withProperties(['old' => $oldData, 'attributes' => $peminjaman->toArray()])
            ->event('returned')
            ->log('Alat berhasil dikembalikan');

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

        activity()
            ->performedOn($peminjaman)
            ->withProperties(['attributes' => $peminjaman->toArray()])
            ->event('deleted')
            ->log('Peminjaman alat dihapus');

        $peminjaman->delete();

        return redirect()->route('peminjaman.index')
            ->with('success', 'Peminjaman berhasil dihapus');
    }
}
