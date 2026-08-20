<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengadaanBahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'update' : 'create';

        $model = $this->route('pengadaan_bahan');
        $model = $model instanceof \App\Models\PengadaanBahan
            ? $model
            : (\App\Models\PengadaanBahan::find($model) ?? \App\Models\PengadaanBahan::class);

        return $this->user()->can($ability, $model);
    }

    public function rules(): array
    {
        return [
            'id_bahan' => ['required', 'exists:bahan,id'],
            'tanggal_pengadaan' => ['required', 'date'],
            'harga_perolehan' => ['required', 'numeric', 'min:0'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'merek' => ['required', 'string', 'max:255'],
            'masa_expire_bahan' => ['nullable', 'date'],
            'supplier' => ['required', 'string', 'max:255'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto_transaksi' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_bahan.required' => 'Bahan harus dipilih',
            'tanggal_pengadaan.required' => 'Tanggal pengadaan tidak boleh kosong',
            'harga_perolehan.required' => 'Harga perolehan tidak boleh kosong',
            'jumlah.required' => 'Jumlah tidak boleh kosong',
            'merek.required' => 'Merek tidak boleh kosong',
            'supplier.required' => 'Supplier tidak boleh kosong',
        ];
    }
}
