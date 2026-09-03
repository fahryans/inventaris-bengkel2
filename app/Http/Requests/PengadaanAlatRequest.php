<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengadaanAlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'update' : 'create';

        $model = $this->route('pengadaan_alat');
        $model = $model instanceof \App\Models\PengadaanAlat
            ? $model
            : (\App\Models\PengadaanAlat::find($model) ?? \App\Models\PengadaanAlat::class);

        return $this->user()->can($ability, $model);
    }

    public function rules(): array
    {
        return [
            'id_alat' => ['required', 'exists:alat,id'],
            'id_spesifikasi_alat' => ['required', 'exists:spesifikasi_alat,id'],
            'kode_inventaris' => ['nullable', 'string', 'max:255'],
            'tanggal_pengadaan' => ['required', 'date'],
            'harga_perolehan' => ['required', 'numeric', 'min:0'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'merek' => ['required', 'string', 'max:255'],
            'supplier' => ['required', 'string', 'max:255'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto_transaksi' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp,bmp,svg,avif,ico,tiff', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_alat.required' => 'Alat harus dipilih',
            'id_spesifikasi_alat.required' => 'Spesifikasi harus dipilih',
            'tanggal_pengadaan.required' => 'Tanggal pengadaan tidak boleh kosong',
            'harga_perolehan.required' => 'Harga perolehan tidak boleh kosong',
            'jumlah.required' => 'Jumlah tidak boleh kosong',
            'merek.required' => 'Merek tidak boleh kosong',
            'supplier.required' => 'Supplier tidak boleh kosong',
        ];
    }
}
