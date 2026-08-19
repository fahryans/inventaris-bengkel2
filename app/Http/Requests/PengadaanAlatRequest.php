<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengadaanAlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'update' : 'create';

        return $this->user()->can($ability, $this->route('pengadaan_alat') ?? \App\Models\PengadaanAlat::class);
    }

    public function rules(): array
    {
        return [
            'id_alat' => ['required', 'exists:alat,id'],
            'tanggal_pengadaan' => ['required', 'date'],
            'harga_perolehan' => ['required', 'numeric', 'min:0'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'supplier' => ['required', 'string', 'max:255'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto_transaksi' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_alat.required' => 'Alat harus dipilih',
            'tanggal_pengadaan.required' => 'Tanggal pengadaan tidak boleh kosong',
            'harga_perolehan.required' => 'Harga perolehan tidak boleh kosong',
            'jumlah.required' => 'Jumlah tidak boleh kosong',
            'supplier.required' => 'Supplier tidak boleh kosong',
        ];
    }
}
