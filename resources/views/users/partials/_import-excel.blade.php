<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-download"></i> Download Template</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Download template Excel untuk memastikan format yang benar.</p>
                <a href="{{ route('users.template-excel') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-file-excel"></i> Download Template
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-upload"></i> Upload File</h6>
            </div>
            <div class="card-body">
                <form id="import-form">
                    <div class="mb-3">
                        <input type="file" class="form-control form-control-sm" name="file"
                               accept=".xlsx,.xls" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload"></i> Upload & Preview
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Preview Section --}}
<div id="preview-section" style="display: none;">
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-eye"></i> Preview Data</h6>
            <div id="preview-stats"></div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered table-sm">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>No. HP</th>
                            <th>No. Induk</th>
                            <th>Password</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="preview-body">
                        {{-- Preview data will be inserted here --}}
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-secondary" onclick="resetImport()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" id="save-import-btn" class="btn btn-primary"
                        onclick="submitImport()" disabled>
                    <i class="fas fa-save"></i> Simpan Yang Valid
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetImport() {
    document.getElementById('preview-section').style.display = 'none';
    document.getElementById('import-form').reset();
}
</script>
