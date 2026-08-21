<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'update' : 'create';

        return $this->user()->can($ability, $this->route('bahan') ?? \App\Models\Bahan::class);
    }

    public function rules(): array
    {
        return [
            'id_kategori' => ['required', 'exists:kategori,id'],
            'id_labor' => ['required', 'exists:laboratorium,id'],
            'nama_bahan' => ['required', 'string', 'max:255'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'satuan' => ['required', 'string', 'max:50'],
            'spesifikasi' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_kategori.required' => 'Kategori harus dipilih',
            'id_labor.required' => 'Laboratorium harus dipilih',
            'nama_bahan.required' => 'Nama bahan tidak boleh kosong',
            'stok_minimum.required' => 'Stok minimum harus diisi',
            'stok_minimum.integer' => 'Stok minimum harus berupa angka',
            'stok_minimum.min' => 'Stok minimum minimal 0',
            'satuan.required' => 'Satuan tidak boleh kosong',
        ];
    }
}
