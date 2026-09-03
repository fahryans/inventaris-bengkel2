<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlatRequest;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\SpesifikasiAlat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class AlatController extends Controller
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
        $this->authorize('viewAny', Alat::class);

        $user = Auth::user();
        $query = Alat::with(['kategori', 'laboratorium', 'spesifikasiAlat'])
            ->withCount(['unitAlat'])  // total seluruh unit fisik
            ->withCount(['unitAlat as unit_alat_tersedia' => fn($q) => $q->where('status', 'tersedia')])
            ->withCount(['unitAlat as unit_alat_pinjam' => fn($q) => $q->where('status', 'dipinjam')])
            ->withSum('pengadaanAlat', 'jumlah')
            ->withSum(['peminjamanAlat' => fn($q) => $q->active()], 'jumlah');

        $labIds = $this->getLabIds();
        if ($labIds) {
            $query->whereIn('id_labor', $labIds);
        }

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->filled('labor')) {
            $query->where('id_labor', $request->labor);
        }

        if ($request->filled('tipe_pelacakan')) {
            $query->where('tipe_pelacakan', $request->tipe_pelacakan);
        }

        if ($request->filled('search')) {
            $query->where('nama_alat', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortParam = $request->get('sort', 'nama_alat');
        $parts = explode('|', $sortParam);
        $sortBy = $parts[0] ?? 'nama_alat';
        $sortDir = $parts[1] ?? 'asc';

        $allowedSorts = ['nama_alat', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama_alat', 'asc');
        }

        $alats = $query->paginate(15)->withQueryString();
        $kategoris = Kategori::where('jenis', 'alat')->get();
        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();

        return view('alat.index', compact('alats', 'kategoris', 'laboratoriums'));
    }

    public function create()
    {
        $this->authorize('create', Alat::class);

        $labIds = $this->getLabIds();
        $kategoris = Kategori::where('jenis', 'alat')->get();
        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();
        $isTeknisi = Auth::user()->role === 'teknisi';
        $autoLab = $isTeknisi ? Auth::user()->laboratoriumTeknisi->first() : null;

        return view('alat.create', compact('kategoris', 'laboratoriums', 'isTeknisi', 'autoLab'));
    }

    public function store(AlatRequest $request)
    {
        $this->authorize('create', Alat::class);

        $validated = $request->validated();

        // Auto-set lab for teknisi
        if (Auth::user()->role === 'teknisi') {
            $validated['id_labor'] = Auth::user()->laboratoriumTeknisi->first()?->id;
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('alat', 'public');
        }

        $alat = Alat::create($validated);

        // Simpan spesifikasi yang langsung ditambahkan bersamaan dengan alat.
        if (!empty($validated['spesifikasi'])) {
            $alat->spesifikasiAlat()->createMany(
                collect($validated['spesifikasi'])->map(fn($s) => [
                    'kode_spesifikasi' => $s['kode_spesifikasi'],
                    'nama_spesifikasi' => $s['nama_spesifikasi'],
                    'deskripsi' => $s['deskripsi'] ?? null,
                ])->all()
            );
        }

        activity()
            ->performedOn($alat)
            ->withProperties(['attributes' => $alat->toArray()])
            ->event('created')
            ->log('Alat baru ditambahkan');

        return redirect()->route('alat.show', $alat)
            ->with('success', 'Alat berhasil ditambahkan');
    }

    public function show(Alat $alat)
    {
        $this->authorize('view', $alat);

        $alat->load([
            'kategori',
            'laboratorium',
            'spesifikasiAlat',
            'unitAlat.spesifikasiAlat',
            'pengadaanAlat.spesifikasiAlat',
            'peminjamanAlat',
        ]);

        return view('alat.show', compact('alat'));
    }

    public function edit(Alat $alat)
    {
        $this->authorize('update', $alat);

        $labIds = $this->getLabIds();
        $kategoris = Kategori::where('jenis', 'alat')->get();
        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();
        $isTeknisi = Auth::user()->role === 'teknisi';
        $autoLab = $isTeknisi ? Auth::user()->laboratoriumTeknisi->first() : null;

        return view('alat.edit', compact('alat', 'kategoris', 'laboratoriums', 'isTeknisi', 'autoLab'));
    }

    public function update(AlatRequest $request, Alat $alat)
    {
        $this->authorize('update', $alat);

        $oldData = $alat->toArray();

        $validated = $request->validated();

        // Auto-set lab for teknisi
        if (Auth::user()->role === 'teknisi') {
            $validated['id_labor'] = Auth::user()->laboratoriumTeknisi->first()?->id;
        }

        if ($request->hasFile('foto')) {
            if ($alat->foto) {
                \Storage::disk('public')->delete($alat->foto);
            }
            $validated['foto'] = $request->file('foto')->store('alat', 'public');
        }

        $alat->update($validated);

        // Handle spesifikasi dari form
        if ($request->has('spesifikasi') && is_array($request->spesifikasi)) {
            // Kode spesifikasi yang dikirim dari form (yang masih dipertahankan)
            $submittedKodes = collect($request->spesifikasi)
                ->filter(fn($s) => !empty($s['kode_spesifikasi']) && !empty($s['nama_spesifikasi']))
                ->pluck('kode_spesifikasi')
                ->values();

            // Hapus spesifikasi existing yang TIDAK ada di form
            // dan tidak dipakai oleh pengadaan manapun
            $alat->spesifikasiAlat()
                ->whereNotIn('kode_spesifikasi', $submittedKodes)
                ->each(function ($spec) {
                    try {
                        $spec->delete();
                    } catch (\Exception $e) {
                        // Jika ada foreign key constraint, skip (spesifikasi masih dipakai pengadaan)
                    }
                });

            // Update atau create spesifikasi dari form
            foreach ($request->spesifikasi as $spec) {
                if (empty($spec['kode_spesifikasi']) || empty($spec['nama_spesifikasi'])) {
                    continue;
                }

                $existing = $alat->spesifikasiAlat()
                    ->where('kode_spesifikasi', $spec['kode_spesifikasi'])
                    ->first();

                if ($existing) {
                    // Update spesifikasi yang sudah ada
                    $existing->update([
                        'nama_spesifikasi' => $spec['nama_spesifikasi'],
                        'deskripsi' => $spec['deskripsi'] ?? null,
                    ]);
                } else {
                    // Buat spesifikasi baru
                    $alat->spesifikasiAlat()->create([
                        'kode_spesifikasi' => $spec['kode_spesifikasi'],
                        'nama_spesifikasi' => $spec['nama_spesifikasi'],
                        'deskripsi' => $spec['deskripsi'] ?? null,
                    ]);
                }
            }
        }

        activity()
            ->performedOn($alat)
            ->withProperties(['old' => $oldData, 'attributes' => $alat->toArray()])
            ->event('updated')
            ->log('Alat diperbarui');

        return redirect()->route('alat.show', $alat)
            ->with('success', 'Alat berhasil diperbarui');
    }

    public function destroy(Alat $alat)
    {
        $this->authorize('delete', $alat);

        activity()
            ->performedOn($alat)
            ->withProperties(['attributes' => $alat->toArray()])
            ->event('deleted')
            ->log('Alat dihapus');

        $alat->delete();

        return redirect()->route('alat.index')
            ->with('success', 'Alat berhasil dihapus');
    }

    // =====================
    // SPEKSIFIKASI MANAGEMENT
    // =====================

    public function storeSpesifikasi(Request $request, Alat $alat)
    {
        $this->authorize('update', $alat);

        $validated = $request->validate([
            'kode_spesifikasi' => ['required', 'string', 'max:255', "unique:spesifikasi_alat,kode_spesifikasi,{$alat->id},id_alat"],
            'nama_spesifikasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $spesifikasi = $alat->spesifikasiAlat()->create($validated);

        activity()
            ->performedOn($spesifikasi)
            ->withProperties(['attributes' => $spesifikasi->toArray()])
            ->event('created')
            ->log('Spesifikasi alat baru ditambahkan');

        return redirect()->route('alat.show', $alat)
            ->with('success', 'Spesifikasi berhasil ditambahkan');
    }

    public function updateSpesifikasi(Request $request, Alat $alat, SpesifikasiAlat $spesifikasi)
    {
        $this->authorize('update', $alat);

        $validated = $request->validate([
            'kode_spesifikasi' => ['required', 'string', 'max:255', "unique:spesifikasi_alat,kode_spesifikasi,{$spesifikasi->id},id"],
            'nama_spesifikasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $spesifikasi->update($validated);

        activity()
            ->performedOn($spesifikasi)
            ->withProperties(['attributes' => $spesifikasi->toArray()])
            ->event('updated')
            ->log('Spesifikasi alat diperbarui');

        return redirect()->route('alat.show', $alat)
            ->with('success', 'Spesifikasi berhasil diperbarui');
    }

    public function destroySpesifikasi(Alat $alat, SpesifikasiAlat $spesifikasi)
    {
        $this->authorize('update', $alat);

        // Cek apakah spesifikasi sudah dipakai di pengadaan atau unit
        if ($spesifikasi->pengadaanAlat()->count() > 0 || $spesifikasi->unitAlat()->count() > 0) {
            return redirect()->route('alat.show', $alat)
                ->with('error', 'Spesifikasi tidak bisa dihapus karena sudah ada data pengadaan atau unit alat');
        }

        activity()
            ->performedOn($spesifikasi)
            ->withProperties(['attributes' => $spesifikasi->toArray()])
            ->event('deleted')
            ->log('Spesifikasi alat dihapus');

        $spesifikasi->delete();

        return redirect()->route('alat.show', $alat)
            ->with('success', 'Spesifikasi berhasil dihapus');
    }
}
