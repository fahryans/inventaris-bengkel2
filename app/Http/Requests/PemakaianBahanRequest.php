<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PemakaianBahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'update' : 'create';

        return $this->user()->can($ability, \App\Models\PemakaianBahan::class);
    }

    public function rules(): array
    {
        return [
            'id_bahan' => ['required', 'exists:bahan,id'],
            'id_pengadaan_bahan' => ['required', 'exists:pengadaan_bahan,id'],
            'keperluan' => ['required', 'string', 'max:255'],
            'jumlah_pengambilan' => ['required', 'integer', 'min:1'],
            'jumlah_terpakai' => ['required', 'integer', 'min:1'],
            'jumlah_pengembalian' => ['nullable', 'integer', 'min:0'],
            'waktu_pemakaian' => ['required', 'date_format:Y-m-d H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_bahan.required' => 'Bahan harus dipilih',
            'id_pengadaan_bahan.required' => 'Pengadaan bahan harus dipilih',
            'keperluan.required' => 'Keperluan tidak boleh kosong',
            'jumlah_pengambilan.required' => 'Jumlah pengambilan tidak boleh kosong',
            'jumlah_terpakai.required' => 'Jumlah terpakai tidak boleh kosong',
            'waktu_pemakaian.required' => 'Waktu pemakaian tidak boleh kosong',
        ];
    }
}
