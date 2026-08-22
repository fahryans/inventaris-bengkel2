<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];

        if ($this->user()->role !== 'mahasiswa') {
            $rules['nama'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }
}
