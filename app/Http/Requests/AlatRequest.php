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
            'merek' => ['nullable', 'string', 'max:255'],
            'spesifikasi' => ['nullable', 'string'],
            'tipe_pelacakan' => ['required', 'in:unit,agregat'],
            'jumlah_alat' => ['required_if:tipe_pelacakan,agregat', 'nullable', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_kategori.required' => 'Kategori harus dipilih',
            'id_labor.required' => 'Laboratorium harus dipilih',
            'nama_alat.required' => 'Nama alat tidak boleh kosong',
            'tipe_pelacakan.required' => 'Tipe pelacakan harus dipilih',
            'jumlah_alat.required_if' => 'Jumlah alat harus diisi untuk tipe agregat',
        ];
    }
}
