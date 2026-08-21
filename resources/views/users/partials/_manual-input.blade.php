<form id="manual-form">
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label fw-bold">Jumlah User</label>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" onclick="addUserRows(5); setActive(this)">5</button>
                <button type="button" class="btn btn-outline-primary" onclick="addUserRows(10); setActive(this)">10</button>
                <button type="button" class="btn btn-outline-primary" onclick="addUserRows(20); setActive(this)">20</button>
                <button type="button" class="btn btn-outline-primary" onclick="addSingleRow(); setActive(this)">+1</button>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">Info</label>
            <div class="alert alert-info py-2 mb-0">
                <i class="fas fa-info-circle"></i> Password default: <strong>nama@123</strong> (bisa diubah per baris)
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="user-table">
            <thead class="table-light">
                <tr>
                    <th width="40">#</th>
                    <th width="150">Nama *</th>
                    <th width="180">Email *</th>
                    <th width="120">Role *</th>
                    <th width="80">Status</th>
                    <th width="100">No. HP</th>
                    <th width="100">No. Induk</th>
                    <th width="120">Password</th>
                    <th width="50">Aksi</th>
                </tr>
            </thead>
            <tbody id="user-table-body">
                {{-- Rows will be added by JavaScript --}}
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Semua
        </button>
    </div>
</form>
