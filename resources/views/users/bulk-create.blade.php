@extends('layouts.admin')

@section('title', 'Tambah User Massal')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Data Users</a></li>
            <li class="breadcrumb-item active">Tambah User Massal</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tambah User Massal</h5>
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card-body">
            {{-- Tabs --}}
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="manual-tab" data-bs-toggle="tab"
                            data-bs-target="#manual-tab-pane" type="button" role="tab">
                        <i class="fas fa-keyboard"></i> Input Manual
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="import-tab" data-bs-toggle="tab"
                            data-bs-target="#import-tab-pane" type="button" role="tab">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                {{-- Tab 1: Manual Input --}}
                <div class="tab-pane fade show active" id="manual-tab-pane" role="tabpanel">
                    @include('users.partials._manual-input')
                </div>

                {{-- Tab 2: Import Excel --}}
                <div class="tab-pane fade" id="import-tab-pane" role="tabpanel">
                    @include('users.partials._import-excel')
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    let rowIndex = 0;
    const roles = @json($roles);
    const defaultPassword = 'nama@123';

    function addUserRows(count) {
        const tbody = document.getElementById('user-table-body');
        const currentRows = tbody.querySelectorAll('tr:not(.empty-row)').length;
        const targetRows = count === 'custom' ? currentRows : parseInt(count);

        // Remove empty row if exists
        const emptyRow = tbody.querySelector('.empty-row');
        if (emptyRow) emptyRow.remove();

        // Add rows
        const rowsToAdd = count === 'custom' ? 1 : targetRows - currentRows;
        for (let i = 0; i < Math.abs(rowsToAdd); i++) {
            if (count !== 'custom' && currentRows + i >= targetRows) break;
            addSingleRow();
        }

        // If custom and we need to remove rows
        if (count === 'custom') {
            // Do nothing, just add one
        }

        updateRowNumbers();
    }

    function addSingleRow() {
        const tbody = document.getElementById('user-table-body');
        const row = document.createElement('tr');
        rowIndex++;

        const roleOptions = roles.map(r => `<option value="${r}">${formatRole(r)}</option>`).join('');

        row.innerHTML = `
            <td class="row-number">${tbody.querySelectorAll('tr').length + 1}</td>
            <td><input type="text" name="users[${rowIndex}][nama]" class="form-control form-control-sm" required></td>
            <td><input type="email" name="users[${rowIndex}][email]" class="form-control form-control-sm" required></td>
            <td>
                <select name="users[${rowIndex}][role]" class="form-select form-select-sm" required>
                    <option value="">Pilih</option>
                    ${roleOptions}
                </select>
            </td>
            <td>
                <select name="users[${rowIndex}][status]" class="form-select form-select-sm" required>
                    <option value="aktif" selected>Aktif</option>
                    <option value="tidak_aktif">Nonaktif</option>
                </select>
            </td>
            <td><input type="text" name="users[${rowIndex}][no_hp]" class="form-control form-control-sm"></td>
            <td><input type="text" name="users[${rowIndex}][no_induk]" class="form-control form-control-sm"></td>
            <td><input type="text" name="users[${rowIndex}][password]" class="form-control form-control-sm" placeholder="Default: nama@123"></td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        updateRowNumbers();
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('#user-table-body tr:not(.empty-row)');
        rows.forEach((row, index) => {
            row.querySelector('.row-number').textContent = index + 1;
        });
    }

    function formatRole(role) {
        const roleNames = {
            'admin_jurusan': 'Admin Jurusan',
            'kepala_labor': 'Kepala Labor',
            'kadep': 'Kepala Departemen',
            'teknisi': 'Teknisi',
            'dosen': 'Dosen',
            'mahasiswa': 'Mahasiswa'
        };
        return roleNames[role] || role;
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('#user-table-body tr');
        rows.forEach((row, index) => {
            row.querySelector('.row-number').textContent = index + 1;
        });
    }

    // Manual Submit
    document.getElementById('manual-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const formData = new FormData(form);
            const users = [];

            // Collect user data
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('users[')) {
                    const match = key.match(/users\[(\d+)\]\[(\w+)\]/);
                    if (match) {
                        const index = match[1];
                        const field = match[2];
                        if (!users[index]) users[index] = {};
                        users[index][field] = value;
                    }
                }
            }

            // Remove empty users
            const filteredUsers = users.filter(u => u && u.nama && u.email);

            const response = await fetch('{{ route("users.bulk-store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ users: filteredUsers }),
            });

            const result = await response.json();

            if (result.success) {
                showToast('success', result.message);
                setTimeout(() => window.location.href = '{{ route("users.index") }}', 1500);
            } else {
                showToast('error', result.message || 'Gagal menyimpan data');
                if (result.errors) {
                    console.error('Errors:', result.errors);
                }
            }
        } catch (error) {
            showToast('error', 'Terjadi kesalahan: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    // Import Submit
    document.getElementById('import-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const fileInput = form.querySelector('input[type="file"]');
        const submitBtn = form.querySelector('button[type="submit"]');

        if (!fileInput.files.length) {
            showToast('error', 'Pilih file Excel terlebih dahulu');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

        try {
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            const response = await fetch('{{ route("users.import-preview") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                showPreview(result);
            } else {
                showToast('error', result.message || 'Gagal memproses file');
            }
        } catch (error) {
            showToast('error', 'Terjadi kesalahan: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Preview';
        }
    });

    function showPreview(data) {
        const previewSection = document.getElementById('preview-section');
        const previewBody = document.getElementById('preview-body');
        const previewStats = document.getElementById('preview-stats');
        const saveBtn = document.getElementById('save-import-btn');

        previewStats.innerHTML = `
            <div class="alert ${data.invalid > 0 ? 'alert-warning' : 'alert-success'}">
                <strong>Total:</strong> ${data.total} user |
                <strong>Valid:</strong> ${data.valid} |
                <strong>Invalid:</strong> ${data.invalid}
            </div>
        `;

        let rows = '';
        data.data.forEach((item, index) => {
            const statusClass = item.valid ? 'table-success' : 'table-danger';
            rows += `
                <tr class="${statusClass}">
                    <td>${index + 1}</td>
                    <td>${item.nama || '-'}</td>
                    <td>${item.email || '-'}</td>
                    <td>${formatRole(item.role) || '-'}</td>
                    <td>${item.status || '-'}</td>
                    <td>${item.no_hp || '-'}</td>
                    <td>${item.no_induk || '-'}</td>
                    <td>${item.password ? '***' : 'Default'}</td>
                    <td>
                        ${item.valid
                            ? '<span class="badge bg-success">Valid</span>'
                            : '<span class="badge bg-danger">' + item.errors.join(', ') + '</span>'
                        }
                    </td>
                </tr>
            `;
        });

        previewBody.innerHTML = rows;
        previewSection.style.display = 'block';

        // Store valid data for submission
        window.importData = data.data.filter(item => item.valid);

        saveBtn.disabled = data.valid === 0;
    }

    async function submitImport() {
        const saveBtn = document.getElementById('save-import-btn');
        const originalText = saveBtn.innerHTML;

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const response = await fetch('{{ route("users.import-store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ users: window.importData }),
            });

            const result = await response.json();

            if (result.success) {
                showToast('success', result.message);
                setTimeout(() => window.location.href = '{{ route("users.index") }}', 1500);
            } else {
                showToast('error', result.message || 'Gagal menyimpan data');
            }
        } catch (error) {
            showToast('error', 'Terjadi kesalahan: ' + error.message);
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }

    function showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    // Initialize with 5 rows
    document.addEventListener('DOMContentLoaded', function() {
        addUserRows(5);
    });
</script>
@endpush
@endsection
