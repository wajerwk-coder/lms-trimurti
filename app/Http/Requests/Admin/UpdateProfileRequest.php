<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'email',
                Rule::unique('users_central', 'email')->ignore(Auth::id()),
            ],
            'phone'    => 'nullable|string|max:20',
            'photo'    => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'       => 'Email sudah digunakan oleh pengguna lain.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'photo.max'          => 'Ukuran foto maksimal 5 MB.',
            'photo.mimes'        => 'Format foto: JPG, PNG, atau WEBP.',
        ];
    }
}
