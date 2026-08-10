<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Kategori::class);
    }

    public function rules(): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'max:255', 'unique:kategori,nama_kategori' . ($this->route('kategori')?->id ? ',' . $this->route('kategori')->id : '')],
            'jenis' => ['required', 'in:alat,bahan'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori tidak boleh kosong',
            'nama_kategori.unique' => 'Nama kategori sudah ada',
            'jenis.required' => 'Jenis harus dipilih',
        ];
    }
}
