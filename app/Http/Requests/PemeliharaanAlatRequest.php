<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PemeliharaanAlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'update' : 'create';

        return $this->user()->can($ability, $this->route('pemeliharaan') ?? \App\Models\PemeliharaanAlat::class);
    }

    public function rules(): array
    {
        return [
            'id_unit_alat' => ['required', 'exists:unit_alat,id'],
            'id_teknisi' => ['required', 'exists:users,id'],
            'tanggal_cek' => ['required', 'date'],
            'tanggal_cek_berikutnya' => ['required', 'date', 'after:tanggal_cek'],
            'kondisi' => ['required', 'string', 'max:255'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'detail_biaya' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'hasil_pemeliharaan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_unit_alat.required' => 'Unit alat harus dipilih',
            'id_teknisi.required' => 'Teknisi harus dipilih',
            'tanggal_cek.required' => 'Tanggal cek tidak boleh kosong',
            'tanggal_cek_berikutnya.required' => 'Tanggal cek berikutnya tidak boleh kosong',
            'tanggal_cek_berikutnya.after' => 'Tanggal cek berikutnya harus setelah tanggal cek',
            'kondisi.required' => 'Kondisi tidak boleh kosong',
        ];
    }
}
