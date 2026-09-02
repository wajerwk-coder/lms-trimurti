<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
            ],
            'role' => 'required|in:admin,guru,siswa',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:L,P',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status' => 'required|in:aktif,nonaktif',
        ];

        // Role-specific validation
        if ($this->role === 'siswa') {
            $rules['nis'] = 'required|string|unique:siswas,nis|max:20';
            $rules['kelas_id'] = 'nullable|exists:kelas,id';
            $rules['nama_ortu'] = 'nullable|string|max:255';
            $rules['no_telepon_ortu'] = 'nullable|string|max:20';
        } elseif ($this->role === 'guru') {
            $rules['nip'] = 'required|string|unique:gurus,nip|max:20';
            $rules['bidang_keahlian'] = 'nullable|string|max:255';
            $rules['sertifikasi'] = 'nullable|string|max:500';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi untuk siswa.',
            'nis.unique' => 'NIS sudah digunakan.',
            'nip.required' => 'NIP wajib diisi untuk guru.',
            'nip.unique' => 'NIP sudah digunakan.',
            'kelas_id.exists' => 'Kelas tidak valid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nis' => 'NIS',
            'nip' => 'NIP',
            'kelas_id' => 'kelas',
            'nama_ortu' => 'nama orang tua',
            'no_telepon_ortu' => 'nomor telepon orang tua',
            'bidang_keahlian' => 'bidang keahlian',
            'sertifikasi' => 'sertifikasi',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Bersihkan nomor telepon
        if ($this->has('phone')) {
            $this->merge(['phone' => preg_replace('/[^0-9]/', '', $this->phone)]);
        }
        if ($this->has('no_telepon_ortu')) {
            $this->merge(['no_telepon_ortu' => preg_replace('/[^0-9]/', '', $this->no_telepon_ortu)]);
        }
    }

    /**
     * Get student data for siswa role.
     */
    public function getSiswaData(): array
    {
        if ($this->role !== 'siswa') {
            return [];
        }

        return [
            'nis' => $this->nis,
            'kelas_id' => $this->kelas_id,
            'nama_ortu' => $this->nama_ortu,
            'no_telepon_ortu' => $this->no_telepon_ortu,
        ];
    }

    /**
     * Get guru data for guru role.
     */
    public function getGuruData(): array
    {
        if ($this->role !== 'guru') {
            return [];
        }

        return [
            'nip' => $this->nip,
            'bidang_keahlian' => $this->bidang_keahlian,
            'sertifikasi' => $this->sertifikasi,
        ];
    }
}