<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
            'role' => ['required', 'in:admin_jurusan,kepala_labor,kadep,teknisi,dosen,mahasiswa'],
            'status' => ['required', 'in:aktif,tidak_aktif'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'no_induk' => ['nullable', 'string', 'max:50', Rule::unique(User::class)->ignore($userId)],
            'password' => $userId ? ['nullable', Rules\Password::defaults()] : ['required', Rules\Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.unique' => 'Email sudah terdaftar',
            'role.required' => 'Role harus dipilih',
            'status.required' => 'Status harus dipilih',
            'password.required' => 'Password tidak boleh kosong',
        ];
    }
}
