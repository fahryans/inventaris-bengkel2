<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaboratoriumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Laboratorium::class);
    }

    public function rules(): array
    {
        return [
            'id_user_kalab' => ['required', 'exists:users,id'],
            'nama_labor' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'sop' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_user_kalab.required' => 'Kepala laboratorium harus dipilih',
            'nama_labor.required' => 'Nama laboratorium tidak boleh kosong',
            'lokasi.required' => 'Lokasi tidak boleh kosong',
        ];
    }
}
