<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitAlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'update' : 'create';

        return $this->user()->can($ability, \App\Models\UnitAlat::class);
    }

    public function rules(): array
    {
        $unitAlat = $this->route('unitAlat');

        return [
            'id_alat' => ['required', 'exists:alat,id'],
            'kode_inventaris' => [
                'required',
                'string',
                'max:255',
                'unique:unit_alat,kode_inventaris' . ($unitAlat ? ',' . $unitAlat->id : ''),
            ],
            'kondisi_saat_ini' => ['required', 'in:baik,rusak_ringan,rusak_berat'],
            'status' => ['required', 'in:tersedia,dipinjam,rusak,maintenance'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_alat.required' => 'Alat harus dipilih',
            'id_alat.exists' => 'Alat tidak valid',
            'kode_inventaris.required' => 'Kode inventaris tidak boleh kosong',
            'kode_inventaris.unique' => 'Kode inventaris sudah digunakan',
            'kondisi_saat_ini.required' => 'Kondisi harus dipilih',
            'kondisi_saat_ini.in' => 'Kondisi tidak valid',
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak valid',
        ];
    }
}
