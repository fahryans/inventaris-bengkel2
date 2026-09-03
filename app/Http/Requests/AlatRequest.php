<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'update' : 'create';

        return $this->user()->can($ability, $this->route('alat') ?? \App\Models\Alat::class);
    }

    public function rules(): array
    {
        return [
            'id_kategori' => ['required', 'exists:kategori,id'],
            'id_labor' => ['required', 'exists:laboratorium,id'],
            'nama_alat' => ['required', 'string', 'max:255'],
            'tipe_pelacakan' => ['required', 'in:unit,agregat'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp,bmp,svg,avif,ico,tiff', 'max:5120'],
            'spesifikasi' => ['nullable', 'array'],
            'spesifikasi.*.kode_spesifikasi' => ['required', 'string', 'max:255'],
            'spesifikasi.*.nama_spesifikasi' => ['required', 'string', 'max:255'],
            'spesifikasi.*.deskripsi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_kategori.required' => 'Kategori harus dipilih',
            'id_labor.required' => 'Laboratorium harus dipilih',
            'nama_alat.required' => 'Nama alat tidak boleh kosong',
            'tipe_pelacakan.required' => 'Tipe pelacakan harus dipilih',
        ];
    }
}
