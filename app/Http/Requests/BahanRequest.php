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
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp,bmp,svg,avif,ico,tiff', 'max:5120'],
            // Spesifikasi validation
            'spesifikasi' => ['nullable', 'array'],
            'spesifikasi.*.kode_spesifikasi' => ['required_with:spesifikasi', 'string', 'max:50'],
            'spesifikasi.*.nama_spesifikasi' => ['required_with:spesifikasi', 'string', 'max:255'],
            'spesifikasi.*.deskripsi' => ['nullable', 'string'],
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
            'spesifikasi.*.kode_spesifikasi.required_with' => 'Kode spesifikasi harus diisi',
            'spesifikasi.*.kode_spesifikasi.max' => 'Kode spesifikasi maksimal 50 karakter',
            'spesifikasi.*.nama_spesifikasi.required_with' => 'Nama spesifikasi harus diisi',
            'spesifikasi.*.nama_spesifikasi.max' => 'Nama spesifikasi maksimal 255 karakter',
        ];
    }
}
