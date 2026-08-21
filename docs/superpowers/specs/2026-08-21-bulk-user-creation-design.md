# Bulk User Creation Design

## Overview

Feature untuk menambahkan multiple user sekaligus melalui halaman users index. Mendukung dua mode: input manual (dynamic rows) dan import Excel dengan preview sebelum submit.

## Goals

- Admin bisa tambah 5, 10, 20+ user sekaligus
- Support input manual dan import Excel
- Default password pattern yang bisa di-custom
- Preview sebelum submit untuk import Excel
- Partial success (beberapa gagal, sisanya tetap tersimpan)

## Architecture

### Approach

**Single Page dengan Tab:**
- Tab 1: Input Manual (pilih jumlah, muncul form rows)
- Tab 2: Import Excel (upload → preview → submit)

### Routes

```
GET  /users/bulk-create      → tampil halaman dengan tab
POST /users/bulk-store       → simpan multiple users dari manual input
POST /users/import-preview   → parse Excel, return preview JSON
POST /users/import-store     → simpan users dari import Excel
GET  /users/template-excel   → download template Excel
```

## UI Design

### Tab 1: Input Manual

```
┌─────────────────────────────────────────────────────────────┐
│  Jumlah User: [5 ▼] [10] [20] [Custom: ___]               │
├─────────────────────────────────────────────────────────────┤
│  Default Password: [nama@123 ▼] (bisa diubah per baris)    │
├─────────────────────────────────────────────────────────────┤
│  # │ Nama │ Email │ Role │ Status │ No.HP │ No.Induk │ Pass │
│  ──┼──────┼───────┼──────┼────────┼───────┼──────────┼──────│
│  1 │      │       │      │        │       │          │      │
│  2 │      │       │      │        │       │          │      │
│  3 │      │       │      │        │       │          │      │
├─────────────────────────────────────────────────────────────┤
│  [+ Tambah Baris]  (kalau custom)                           │
│                                   [ Simpan Semua ]          │
└─────────────────────────────────────────────────────────────┘
```

### Tab 2: Import Excel

```
┌─────────────────────────────────────────────────────────────┐
│  [ Download Template Excel ]                                │
│  Drag & drop file atau [ Browse ]                           │
├─────────────────────────────────────────────────────────────┤
│  Preview: 5 dari 10 user valid                              │
│  ┌─────┬───────┬─────────┬──────┬────────┬──────┬────────┐ │
│  │ #   │ Nama  │ Email   │ Role │ Status │ Valid│ Error  │ │
│  ├─────┼───────┼─────────┼──────┼────────┼──────┼────────┤ │
│  │ 1   │ Budi  │ budi@.. │ Dosen│ Aktif  │  ✓   │        │ │
│  │ 2   │ Andi  │ andi@.. │ Mhs  │ Aktif  │  ✓   │        │ │
│  │ 3   │       │ duplikat│ -    │ -      │  ✗   │ Email  │ │
│  └─────┴───────┴─────────┴──────┴────────┴──────┴────────┘ │
│  [Download Error Report]                                    │
│                                   [ Simpan Yang Valid ]     │
└─────────────────────────────────────────────────────────────┘
```

## Backend Logic

### Controller Methods

```php
// UserController.php

public function bulkCreate()
{
    $this->authorize('create', User::class);
    return view('users.bulk-create');
}

public function bulkStore(Request $request)
{
    $validated = $request->validate([
        'users' => ['required', 'array', 'min:1'],
        'users.*.nama' => ['required', 'string', 'max:255'],
        'users.*.email' => ['required', 'email', 'unique:users,email'],
        'users.*.role' => ['required', 'in:admin_jurusan,kepala_labor,kadep,teknisi,dosen,mahasiswa'],
        'users.*.status' => ['required', 'in:aktif,tidak_aktif'],
        'users.*.no_hp' => ['nullable', 'string', 'max:20'],
        'users.*.no_induk' => ['nullable', 'string', 'max:50', 'unique:users,no_induk'],
        'users.*.password' => ['nullable', 'string', 'min:8'],
    ]);

    $created = 0;
    $failed = 0;
    $errors = [];

    foreach ($validated['users'] as $index => $userData) {
        try {
            if (empty($userData['password'])) {
                $userData['password'] = $this->generatePassword($userData['nama']);
            }
            $userData['password'] = Hash::make($userData['password']);
            User::create($userData);
            $created++;
        } catch (\Exception $e) {
            $failed++;
            $errors[] = ['row' => $index + 1, 'message' => $e->getMessage()];
        }
    }

    return response()->json([
        'success' => $created > 0,
        'message' => "{$created} dari " . count($validated['users']) . " user berhasil ditambahkan",
        'created' => $created,
        'failed' => $failed,
        'errors' => $errors,
    ]);
}

public function importPreview(Request $request)
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
    ]);

    $import = new UsersImport();
    $preview = Excel::toCollection([], $request->file('file'))->first();

    $results = [];
    foreach ($preview as $index => $row) {
        $validation = $this->validateImportRow($row);
        $results[] = [
            'row' => $index + 1,
            'data' => $row->toArray(),
            'valid' => $validation['valid'],
            'errors' => $validation['errors'],
        ];
    }

    return response()->json($results);
}

public function importStore(Request $request)
{
    $request->validate([
        'users' => ['required', 'array', 'min:1'],
    ]);

    $created = 0;
    $failed = 0;

    foreach ($request->users as $userData) {
        if (empty($userData['password'])) {
            $userData['password'] = $this->generatePassword($userData['nama']);
        }
        $userData['password'] = Hash::make($userData['password']);

        try {
            User::create($userData);
            $created++;
        } catch (\Exception $e) {
            $failed++;
        }
    }

    return response()->json([
        'success' => $created > 0,
        'message' => "{$created} user berhasil ditambahkan",
        'created' => $created,
        'failed' => $failed,
    ]);
}

public function downloadTemplate()
{
    return Excel::download(new UsersTemplateExport, 'template_users.xlsx');
}

private function generatePassword(string $name): string
{
    $clean = preg_replace('/\s+/', '', strtolower($name));
    return $clean . '@123';
}
```

### Validation Strategy

**Manual Input:**
- Validate per row, collect errors
- Partial success: baris valid tersimpan, baris error di-skip
- Return JSON dengan detail success/failed

**Import Excel:**
- Parse dengan Maatwebsite/Excel
- Validate each row
- Return preview dengan valid/invalid status
- Only save rows that pass validation
- Generate error report untuk failed rows

### Password Generation

```
Default: {nama_lowercase_no_space}@123
Example: "Budi Santoso" → "budisantoso@123"
Admin bisa override per baris atau global pattern
```

## Error Handling

### Validation Errors

**Manual Input:**
- Jika ada error, form tetap terisi (kecuali password)
- Tampilkan error per baris
- Baris yang error tidak disimpan

**Import Excel:**
```
Preview Result:
├── Total rows: 15
├── Valid: 12 rows → bisa disimpan
├── Invalid: 3 rows → ditampilkan di table merah
│   ├── Row 2: Email "budi@x.com" sudah terdaftar
│   ├── Row 8: Role "supir" tidak valid
│   └── Row 11: Email format salah
└── [Download Error Report] → Excel file dengan kolom "Error Message"
```

### Edge Cases

| Case | Handling |
|------|----------|
| Email duplikat | Skip row, log error, lanjut row lain |
| Email format invalid | Skip row, log error |
| Role tidak valid | Skip row, log error |
| Password kosong | Gunakan default pattern |
| No HP duplicate | Boleh (bukan unique) |
| No Induk duplicate | Skip row, log error |
| Empty row | Skip automatic |
| File Excel corrupt | Return error message |
| File bukan Excel | Return error "Format file tidak valid" |
| Duplicate within batch | Cek email duplikat dalam 1 upload, reject |

### Partial Success Response

```json
{
    "success": true,
    "message": "12 dari 15 user berhasil ditambahkan",
    "created": 12,
    "failed": 3,
    "errors": [
        {"row": 3, "message": "Email sudah terdaftar"},
        {"row": 8, "message": "Role tidak valid"},
        {"row": 11, "message": "Email format salah"}
    ]
}
```

## File Structure

### New Files

```
app/
├── Http/Controllers/
│   └── UserController.php          (update - tambah bulk methods)
├── Imports/
│   └── UsersImport.php            (Maatwebsite/Excel import class)
├── Exports/
│   └── UsersTemplateExport.php    (template Excel download)

resources/views/users/
├── bulk-create.blade.php          (NEW - tab container)
├── partials/
│   ├── _manual-input.blade.php    (NEW - tab 1 content)
│   ├── _import-excel.blade.php    (NEW - tab 2 content)
│   └── _preview-table.blade.php   (NEW - import preview)
```

### Modified Files

```
routes/web.php                     (tambah 4 routes baru)
composer.json                      (tambah maatwebsite/excel)
app/Http/Requests/UserRequest.php  (tambah bulk validation rules)
```

### Dependencies

```
maatwebsite/excel ^3.1
```

## Testing

### Unit Tests

```php
tests/Unit/Services/BulkUserServiceTest.php
├── test_bulk_store_creates_multiple_users
├── test_bulk_store_handles_duplicate_email
├── test_bulk_store_uses_default_password
├── test_bulk_store_allows_custom_password
└── test_import_preview_validates_rows
```

### Feature Tests

```php
tests/Feature/Controllers/UserControllerBulkTest.php
├── test_bulk_create_page_requires_auth
├── test_bulk_create_page_returns_200
├── test_bulk_store_validates_required_fields
├── test_bulk_store_creates_users
├── test_import_preview_returns_preview_data
├── test_import_store_creates_valid_users
├── test_import_store_rejects_invalid_file
└── test_download_template_returns_excel
```

### Test Data

- Create users with various roles
- Test duplicate email detection
- Test invalid role handling
- Test password generation
- Test partial success scenario

## Activity Log

```php
activity()
    ->performedOn($user)
    ->event('created')
    ->log('User ditambahkan via bulk input');
```

## Implementation Order

1. Install Maatwebsite/Excel
2. Create routes
3. Create UsersImport class
4. Create UsersTemplateExport class
5. Create bulk-create.blade.php
6. Create partials (_manual-input, _import-excel, _preview-table)
7. Update UserController with bulk methods
8. Write tests
9. Test manually
10. Commit
