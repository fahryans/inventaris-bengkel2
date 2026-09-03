<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpesifikasiBahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_spesifikasi' => ['required', 'string', 'max:50'],
            'nama_spesifikasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_spesifikasi.required' => 'Kode spesifikasi harus diisi',
            'kode_spesifikasi.max' => 'Kode spesifikasi maksimal 50 karakter',
            'nama_spesifikasi.required' => 'Nama spesifikasi harus diisi',
            'nama_spesifikasi.max' => 'Nama spesifikasi maksimal 255 karakter',
        ];
    }
}
