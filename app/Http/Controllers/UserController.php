<?php

namespace App\Http\Controllers;

use App\Exports\UsersTemplateExport;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Activitylog\Facades\Activity;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->paginate(15);
        $roles = ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa'];
        $statuses = ['aktif', 'tidak_aktif'];

        return view('users.index', compact('users', 'roles', 'statuses'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa'];

        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $logData = $user->toArray();
        unset($logData['password']);
        unset($logData['remember_token']);

        activity()
            ->performedOn($user)
            ->withProperties(['attributes' => $logData])
            ->event('created')
            ->log('User baru ditambahkan');

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['laboratoriumDikelola', 'pengadaanAlat', 'pengadaanBahan', 'peminjamanAlat', 'pemakaianBahan', 'pemeliharaanAlat']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa'];
        $statuses = ['aktif', 'tidak_aktif'];

        return view('users.edit', compact('user', 'roles', 'statuses'));
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $oldData = $user->toArray();
        unset($oldData['password']);
        unset($oldData['remember_token']);

        $validated = $request->validated();

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $newData = $user->toArray();
        unset($newData['password']);
        unset($newData['remember_token']);

        activity()
            ->performedOn($user)
            ->withProperties(['old' => $oldData, 'attributes' => $newData])
            ->event('updated')
            ->log('User diperbarui');

        return redirect()->route('users.show', $user)
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $logData = $user->toArray();
        unset($logData['password']);
        unset($logData['remember_token']);

        activity()
            ->performedOn($user)
            ->withProperties(['attributes' => $logData])
            ->event('deleted')
            ->log('User dihapus');

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }

    // Bulk User Creation Methods

    public function bulkCreate()
    {
        $this->authorize('create', User::class);

        $roles = ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa'];

        return view('users.bulk-create', compact('roles'));
    }

    public function bulkStore(Request $request)
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'users' => ['required', 'array', 'min:1'],
            'users.*.nama' => ['required', 'string', 'max:255'],
            'users.*.email' => ['required', 'email', 'max:255'],
            'users.*.role' => ['required', 'in:admin_jurusan,kepala_labor,kadep,teknisi,dosen,mahasiswa'],
            'users.*.status' => ['required', 'in:aktif,tidak_aktif'],
            'users.*.no_hp' => ['nullable', 'string', 'max:20'],
            'users.*.no_induk' => ['nullable', 'string', 'max:50'],
            'users.*.password' => ['nullable', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $created = 0;
        $failed = 0;
        $errors = [];
        $emailsInBatch = [];

        foreach ($request->users as $index => $userData) {
            // Check duplicate email within batch
            if (in_array($userData['email'], $emailsInBatch)) {
                $failed++;
                $errors[] = [
                    'row' => $index + 1,
                    'message' => 'Email "' . $userData['email'] . '" duplikat dalam batch',
                ];
                continue;
            }

            // Check email uniqueness in database
            if (User::where('email', $userData['email'])->exists()) {
                $failed++;
                $errors[] = [
                    'row' => $index + 1,
                    'message' => 'Email "' . $userData['email'] . '" sudah terdaftar',
                ];
                continue;
            }

            // Check no_induk uniqueness if provided
            if (!empty($userData['no_induk']) && User::where('no_induk', $userData['no_induk'])->exists()) {
                $failed++;
                $errors[] = [
                    'row' => $index + 1,
                    'message' => 'No. Induk "' . $userData['no_induk'] . '" sudah terdaftar',
                ];
                continue;
            }

            try {
                $password = !empty($userData['password'])
                    ? $userData['password']
                    : $this->generatePassword($userData['nama']);

                $userData['password'] = Hash::make($password);
                $userData['default_password'] = $password;

                User::create($userData);
                $emailsInBatch[] = $userData['email'];
                $created++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $index + 1,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => $created > 0,
            'message' => "{$created} dari " . count($request->users) . " user berhasil ditambahkan",
            'created' => $created,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }

    public function importPreview(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel kosong atau tidak ada data',
                ], 422);
            }

            // Remove header row
            $header = array_shift($rows);

            $results = [];
            $validCount = 0;
            $invalidCount = 0;

            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }

                $validation = $this->validateImportRow($row, $index + 2);

                $results[] = [
                    'row' => $index + 2,
                    'nama' => $row[0] ?? '',
                    'email' => $row[1] ?? '',
                    'role' => $row[2] ?? '',
                    'status' => $row[3] ?? '',
                    'no_hp' => $row[4] ?? '',
                    'no_induk' => $row[5] ?? '',
                    'password' => $row[6] ?? '',
                    'valid' => $validation['valid'],
                    'errors' => $validation['errors'],
                ];

                if ($validation['valid']) {
                    $validCount++;
                } else {
                    $invalidCount++;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'total' => count($results),
                'valid' => $validCount,
                'invalid' => $invalidCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function importStore(Request $request)
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'users' => ['required', 'array', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $created = 0;
        $failed = 0;
        $errors = [];

        foreach ($request->users as $index => $userData) {
            // Skip invalid rows
            if (!empty($userData['errors'])) {
                continue;
            }

            try {
                $password = !empty($userData['password'])
                    ? $userData['password']
                    : $this->generatePassword($userData['nama']);

                $user = User::create([
                    'nama' => $userData['nama'],
                    'email' => $userData['email'],
                    'role' => $userData['role'],
                    'status' => $userData['status'] ?? 'aktif',
                    'no_hp' => $userData['no_hp'] ?? null,
                    'no_induk' => $userData['no_induk'] ?? null,
                    'password' => Hash::make($password),
                ]);

                $created++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $index + 1,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => $created > 0,
            'message' => "{$created} user berhasil ditambahkan",
            'created' => $created,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('create', User::class);

        $export = new UsersTemplateExport();
        return $export->download();
    }

    private function generatePassword(string $name): string
    {
        $clean = preg_replace('/\s+/', '', strtolower($name));
        return $clean . '@123';
    }

    private function validateImportRow(array $row, int $lineNumber): array
    {
        $errors = [];
        $validRoles = ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa'];
        $validStatuses = ['aktif', 'tidak_aktif'];

        // Check required fields
        if (empty($row[0])) {
            $errors[] = 'Nama harus diisi';
        }
        if (empty($row[1])) {
            $errors[] = 'Email harus diisi';
        } elseif (!filter_var($row[1], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } elseif (User::where('email', $row[1])->exists()) {
            $errors[] = 'Email sudah terdaftar';
        }
        if (empty($row[2])) {
            $errors[] = 'Role harus diisi';
        } elseif (!in_array($row[2], $validRoles)) {
            $errors[] = 'Role tidak valid';
        }
        if (!empty($row[3]) && !in_array($row[3], $validStatuses)) {
            $errors[] = 'Status tidak valid';
        }
        if (!empty($row[5]) && User::where('no_induk', $row[5])->exists()) {
            $errors[] = 'No. Induk sudah terdaftar';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
