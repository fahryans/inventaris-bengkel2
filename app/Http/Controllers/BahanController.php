<?php

namespace App\Http\Controllers;

use App\Http\Requests\BahanRequest;
use App\Http\Requests\SpesifikasiBahanRequest;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\SpesifikasiBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

class BahanController extends Controller
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
        $this->authorize('viewAny', Bahan::class);

        $user = Auth::user();
        $query = Bahan::with(['kategori', 'laboratorium'])
            ->with(['pengadaanBahan' => fn($q) => $q->select('id', 'id_bahan', 'merek')])
            ->withSum('pengadaanBahan', 'stok_tersisa_batch');

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

        if ($request->filled('search')) {
            $query->where('nama_bahan', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortParam = $request->get('sort', 'nama_bahan');
        $parts = explode('|', $sortParam);
        $sortBy = $parts[0] ?? 'nama_bahan';
        $sortDir = $parts[1] ?? 'asc';

        $allowedSorts = ['nama_bahan', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama_bahan', 'asc');
        }

        $bahans = $query->paginate(15)->withQueryString();

        // Kategori yang benar-benar dipakai oleh bahan di lab terfilter.
        $kategoris = Kategori::where('jenis', 'bahan')
            ->when($labIds, fn($q) => $q->whereIn('id', Bahan::whereIn('id_labor', $labIds)->pluck('id_kategori')->unique()))
            ->get();

        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();

        // Teknisi/kalab: daftar & filter sudah terbatas pada lab-nya, filter lab tidak perlu.
        $showLabFilter = $labIds === null;

        return view('bahan.index', compact('bahans', 'kategoris', 'laboratoriums', 'showLabFilter'));
    }

    public function create()
    {
        $this->authorize('create', Bahan::class);

        $labIds = $this->getLabIds();
        $kategoris = Kategori::where('jenis', 'bahan')->get();
        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();
        $isTeknisi = Auth::user()->role === 'teknisi';
        $autoLab = $isTeknisi ? Auth::user()->laboratoriumTeknisi->first() : null;

        return view('bahan.create', compact('kategoris', 'laboratoriums', 'isTeknisi', 'autoLab'));
    }

    public function store(BahanRequest $request)
    {
        \Log::info('BahanController@store called', [
            'user' => Auth::user()?->id,
            'role' => Auth::user()?->role
        ]);

        try {
            $this->authorize('create', Bahan::class);
            \Log::info('Authorization passed');
        } catch (\Exception $e) {
            \Log::error('Authorization failed: ' . $e->getMessage());
            throw $e;
        }

        try {
            $validated = $request->validated();
            \Log::info('Validation passed', $validated);
        } catch (\Exception $e) {
            \Log::error('Validation failed: ' . $e->getMessage());
            throw $e;
        }

        // Auto-set lab for teknisi
        if (Auth::user()->role === 'teknisi') {
            $validated['id_labor'] = Auth::user()->laboratoriumTeknisi->first()?->id;
            \Log::info('Lab auto-set for teknisi: ' . $validated['id_labor']);
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('bahan', 'public');
            \Log::info('Foto uploaded: ' . $validated['foto']);
        }

        try {
            $bahan = Bahan::create($validated);
            \Log::info('Bahan created successfully', ['id' => $bahan->id]);
        } catch (\Exception $e) {
            \Log::error('Failed to create bahan: ' . $e->getMessage(), [
                'data' => $validated,
                'error' => $e
            ]);
            throw $e;
        }

        // Handle spesifikasi dari form
        if ($request->has('spesifikasi') && is_array($request->spesifikasi)) {
            foreach ($request->spesifikasi as $spec) {
                if (!empty($spec['kode_spesifikasi']) && !empty($spec['nama_spesifikasi'])) {
                    SpesifikasiBahan::create([
                        'id_bahan' => $bahan->id,
                        'kode_spesifikasi' => $spec['kode_spesifikasi'],
                        'nama_spesifikasi' => $spec['nama_spesifikasi'],
                        'deskripsi' => $spec['deskripsi'] ?? null,
                    ]);
                }
            }
            \Log::info('Spesifikasi created', ['count' => count($request->spesifikasi)]);
        }

        activity()
            ->performedOn($bahan)
            ->withProperties(['attributes' => $bahan->toArray()])
            ->event('created')
            ->log('Bahan baru ditambahkan');

        return redirect()->route('bahan.index')
            ->with('success', 'Bahan berhasil ditambahkan');
    }

    public function show(Bahan $bahan)
    {
        $this->authorize('view', $bahan);

        $bahan->load([
            'kategori',
            'laboratorium',
            'spesifikasiBahan.pengadaanBahan',
            'pengadaanBahan.spesifikasiBahan',
            'pemakaianBahan' => fn($q) => $q->latest(),
        ]);

        return view('bahan.show', compact('bahan'));
    }

    public function edit(Bahan $bahan)
    {
        $this->authorize('update', $bahan);

        $labIds = $this->getLabIds();
        $kategoris = Kategori::where('jenis', 'bahan')->get();
        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();
        $isTeknisi = Auth::user()->role === 'teknisi';
        $autoLab = $isTeknisi ? Auth::user()->laboratoriumTeknisi->first() : null;

        return view('bahan.edit', compact('bahan', 'kategoris', 'laboratoriums', 'isTeknisi', 'autoLab'));
    }

    public function update(BahanRequest $request, Bahan $bahan)
    {
        $this->authorize('update', $bahan);

        $oldData = $bahan->toArray();

        $validated = $request->validated();

        // Auto-set lab for teknisi
        if (Auth::user()->role === 'teknisi') {
            $validated['id_labor'] = Auth::user()->laboratoriumTeknisi->first()?->id;
        }

        if ($request->hasFile('foto')) {
            if ($bahan->foto) {
                \Storage::disk('public')->delete($bahan->foto);
            }
            $validated['foto'] = $request->file('foto')->store('bahan', 'public');
        }

        $bahan->update($validated);

        // Handle spesifikasi dari form
        if ($request->has('spesifikasi') && is_array($request->spesifikasi)) {
            // Kode spesifikasi yang dikirim dari form (yang masih dipertahankan)
            $submittedKodes = collect($request->spesifikasi)
                ->filter(fn($s) => !empty($s['kode_spesifikasi']) && !empty($s['nama_spesifikasi']))
                ->pluck('kode_spesifikasi')
                ->values();

            // Hapus spesifikasi existing yang TIDAK ada di form
            // dan tidak dipakai oleh pengadaan manapun
            $bahan->spesifikasiBahan()
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

                $existing = $bahan->spesifikasiBahan()
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
                    SpesifikasiBahan::create([
                        'id_bahan' => $bahan->id,
                        'kode_spesifikasi' => $spec['kode_spesifikasi'],
                        'nama_spesifikasi' => $spec['nama_spesifikasi'],
                        'deskripsi' => $spec['deskripsi'] ?? null,
                    ]);
                }
            }
        }

        activity()
            ->performedOn($bahan)
            ->withProperties(['old' => $oldData, 'attributes' => $bahan->toArray()])
            ->event('updated')
            ->log('Bahan diperbarui');

        return redirect()->route('bahan.show', $bahan)
            ->with('success', 'Bahan berhasil diperbarui');
    }

    public function destroy(Bahan $bahan)
    {
        $this->authorize('delete', $bahan);

        activity()
            ->performedOn($bahan)
            ->withProperties(['attributes' => $bahan->toArray()])
            ->event('deleted')
            ->log('Bahan dihapus');

        $bahan->delete();

        return redirect()->route('bahan.index')
            ->with('success', 'Bahan berhasil dihapus');
    }

    /**
     * Store spesifikasi bahan
     */
    public function storeSpesifikasi(SpesifikasiBahanRequest $request, Bahan $bahan)
    {
        $this->authorize('update', $bahan);

        $validated = $request->validated();
        $validated['id_bahan'] = $bahan->id;

        // Check kode_spesifikasi sudah ada atau belum
        $exists = SpesifikasiBahan::where('id_bahan', $bahan->id)
            ->where('kode_spesifikasi', $validated['kode_spesifikasi'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'kode_spesifikasi' => 'Kode spesifikasi sudah ada untuk bahan ini'
            ]);
        }

        $spesifikasi = SpesifikasiBahan::create($validated);

        activity()
            ->performedOn($spesifikasi)
            ->withProperties(['attributes' => $spesifikasi->toArray()])
            ->event('created')
            ->log('Spesifikasi bahan ditambahkan');

        return back()->with('success', 'Spesifikasi berhasil ditambahkan');
    }

    /**
     * Update spesifikasi bahan
     */
    public function updateSpesifikasi(SpesifikasiBahanRequest $request, Bahan $bahan, SpesifikasiBahan $spesifikasi)
    {
        $this->authorize('update', $bahan);

        if ($spesifikasi->id_bahan !== $bahan->id) {
            abort(404);
        }

        $validated = $request->validated();
        $oldData = $spesifikasi->toArray();

        $spesifikasi->update($validated);

        activity()
            ->performedOn($spesifikasi)
            ->withProperties(['old' => $oldData, 'attributes' => $spesifikasi->toArray()])
            ->event('updated')
            ->log('Spesifikasi bahan diperbarui');

        return back()->with('success', 'Spesifikasi berhasil diperbarui');
    }

    /**
     * Delete spesifikasi bahan
     */
    public function destroySpesifikasi(Bahan $bahan, SpesifikasiBahan $spesifikasi)
    {
        $this->authorize('update', $bahan);

        if ($spesifikasi->id_bahan !== $bahan->id) {
            abort(404);
        }

        // Proteksi: tidak bisa hapus jika sudah dipakai di pengadaan
        if ($spesifikasi->pengadaanBahan()->exists()) {
            return back()->with('error', 'Spesifikasi tidak dapat dihapus karena sudah dipakai di pengadaan bahan');
        }

        activity()
            ->performedOn($spesifikasi)
            ->withProperties(['attributes' => $spesifikasi->toArray()])
            ->event('deleted')
            ->log('Spesifikasi bahan dihapus');

        $spesifikasi->delete();

        return back()->with('success', 'Spesifikasi berhasil dihapus');
    }
}
