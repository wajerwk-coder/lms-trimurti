<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'siswa'; // ✅ Perbaikan: 'student' → 'siswa'
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($userId)
            ],
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'current_password' => 'required_with:password|current_password',
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
            ],
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:15',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'allergies' => 'nullable|string|max:500',
            'medical_conditions' => 'nullable|string|max:1000',
            'emergency_contact' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'name.string' => 'Name must be text.',
            'name.max' => 'Name cannot exceed 255 characters.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email already registered.',

            'phone.string' => 'Phone number must be text.',
            'phone.max' => 'Phone number cannot exceed 15 characters.',

            'address.string' => 'Address must be text.',
            'address.max' => 'Address cannot exceed 500 characters.',

            'birth_date.date' => 'Birth date must be a valid date.',
            'birth_date.before' => 'Birth date must be before today.',

            'gender.in' => 'Gender must be Male or Female.',

            'photo.image' => 'Photo must be an image.',
            'photo.mimes' => 'Photo must be in format: jpeg, png, jpg, gif, webp.',
            'photo.max' => 'Photo size cannot exceed 2MB.',

            'current_password.required_with' => 'Current password is required when changing password.',
            'current_password.current_password' => 'Current password is incorrect.',

            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',

            'parent_name.string' => 'Parent name must be text.',
            'parent_name.max' => 'Parent name cannot exceed 255 characters.',

            'parent_phone.string' => 'Parent phone must be text.',
            'parent_phone.max' => 'Parent phone cannot exceed 15 characters.',

            'blood_type.in' => 'Blood type must be A, B, AB, or O.',

            'allergies.string' => 'Allergies must be text.',
            'allergies.max' => 'Allergies cannot exceed 500 characters.',

            'medical_conditions.string' => 'Medical conditions must be text.',
            'medical_conditions.max' => 'Medical conditions cannot exceed 1000 characters.',

            'emergency_contact.string' => 'Emergency contact must be text.',
            'emergency_contact.max' => 'Emergency contact cannot exceed 20 characters.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'phone' => 'phone number',
            'address' => 'address',
            'birth_date' => 'birth date',
            'gender' => 'gender',
            'photo' => 'photo',
            'current_password' => 'current password',
            'password' => 'new password',
            'parent_name' => 'parent name',
            'parent_phone' => 'parent phone',
            'blood_type' => 'blood type',
            'allergies' => 'allergies',
            'medical_conditions' => 'medical conditions',
            'emergency_contact' => 'emergency contact',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => preg_replace('/[^0-9]/', '', $this->phone)]);
        }

        if ($this->has('parent_phone')) {
            $this->merge(['parent_phone' => preg_replace('/[^0-9]/', '', $this->parent_phone)]);
        }

        if ($this->has('emergency_contact')) {
            $this->merge(['emergency_contact' => preg_replace('/[^0-9]/', '', $this->emergency_contact)]);
        }

        $nullableFields = [
            'phone', 'address', 'birth_date', 'gender',
            'parent_name', 'parent_phone', 'blood_type',
            'allergies', 'medical_conditions', 'emergency_contact'
        ];

        foreach ($nullableFields as $field) {
            if ($this->has($field) && empty(trim($this->$field))) {
                $this->merge([$field => null]);
            }
        }

        if ($this->has('password') && empty($this->password)) {
            $this->merge(['password' => null]);
            $this->request->remove('password_confirmation');
        }
    }

    public function getStudentData(): array
    {
        return [
            'parent_name' => $this->parent_name,
            'parent_phone' => $this->parent_phone,
            'blood_type' => $this->blood_type,
            'allergies' => $this->allergies,
            'medical_conditions' => $this->medical_conditions,
            'emergency_contact' => $this->emergency_contact,
        ];
    }
}
