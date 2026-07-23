<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // <-- Agrega esta línea
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre del usuario.',
            'name.max' => 'El nombre no debe tener más de 255 caracteres.',
            'email.required' => 'Escribe el correo del usuario.',
            'email.email' => 'El correo no tiene formato válido.',
            'email.unique' => 'Ese correo ya está registrado en otra cuenta.',
        ];
    }
}
