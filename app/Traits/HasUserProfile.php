<?php

namespace App\Traits;

use App\Models\Siswa;
use App\Models\Guru;

trait HasUserProfile
{
    /**
     * Get the user's profile data based on their role
     */
    public function getProfileData()
    {
        if ($this->isSiswa() && $this->siswa) {
            return $this->siswa;
        } elseif ($this->isGuru() && $this->guru) {
            return $this->guru;
        }

        return null;
    }

    /**
     * Get the user's full name from profile or user model
     */
    public function getFullNameAttribute(): string
    {
        $profile = $this->getProfileData();
        
        if ($profile) {
            return $profile->nama ?? $this->name;
        }

        return $this->name;
    }

    /**
     * Get the user's age from profile or user model
     */
    public function getAgeAttribute(): ?int
    {
        $profile = $this->getProfileData();
        
        if ($profile && isset($profile->date_lahir)) {
            return now()->diffInYears($profile->date_lahir);
        }

        if ($this->birth_date) {
            return now()->diffInYears($this->birth_date);
        }

        return null;
    }

    /**
     * Get the user's formatted name with title based on role
     */
    public function getFormattedName(): string
    {
        if ($this->isGuru()) {
            return "Guru {$this->name}";
        } elseif ($this->isSiswa()) {
            return "Siswa {$this->name}";
        }

        return $this->name;
    }

    /**
     * Get the user's contact information
     */
    public function getContactInfo(): array
    {
        $profile = $this->getProfileData();
        
        return [
            'phone' => $profile->no_telepon ?? $this->phone,
            'email' => $profile->email ?? $this->email,
            'address' => $profile->alamat ?? $this->address,
        ];
    }

    /**
     * Get the user's academic information (for students)
     */
    public function getAcademicInfo(): ?array
    {
        if (!$this->isSiswa() || !$this->siswa) {
            return null;
        }

        return [
            'nis' => $this->siswa->nis,
            'nisn' => $this->siswa->nisn,
            'kelas' => $this->siswa->kelas,
            'major' => $this->siswa->major,
            'tahun_ajaran' => $this->siswa->tahun_ajaran,
        ];
    }

    /**
     * Get the user's professional information (for teachers)
     */
    public function getProfessionalInfo(): ?array
    {
        if (!$this->isGuru() || !$this->guru) {
            return null;
        }

        return [
            'nip' => $this->guru->nip,
            'mata_pelajaran' => $this->guru->mata_pelajaran,
            'pendidikan_terakhir' => $this->guru->pendidikan_terakhir,
        ];
    }

    /**
     * Get the user's health information (for students)
     */
    public function getHealthInfo(): ?array
    {
        if (!$this->isSiswa() || !$this->siswa) {
            return null;
        }

        return [
            'golongan_darah' => $this->siswa->golongan_darah,
            'riwayat_penyakit' => $this->siswa->riwayat_penyakit,
            'alergi' => $this->siswa->alergi,
            'info_kesehatan' => $this->siswa->info_kesehatan,
        ];
    }

    /**
     * Get the user's parent information (for students)
     */
    public function getParentInfo(): ?array
    {
        if (!$this->isSiswa() || !$this->siswa) {
            return null;
        }

        return [
            'nama_ortu' => $this->siswa->nama_ortu,
            'no_telepon_ortu' => $this->siswa->no_telepon_ortu,
        ];
    }

    /**
     * Check if user has complete profile
     */
    public function hasCompleteProfile(): bool
    {
        $profile = $this->getProfileData();
        
        if (!$profile) {
            return false;
        }

        // Check required fields based on role
        if ($this->isSiswa()) {
            return !empty($profile->nis) && 
                   !empty($profile->tempat_lahir) && 
                   !empty($profile->date_lahir) &&
                   !empty($profile->kelas_id);
        }

        if ($this->isGuru()) {
            return !empty($profile->nip) && 
                   !empty($profile->mata_pelajaran) && 
                   !empty($profile->pendidikan_terakhir);
        }

        return true;
    }

    /**
     * Get profile completion percentage
     */
    public function getProfileCompletionPercentage(): int
    {
        $profile = $this->getProfileData();
        
        if (!$profile) {
            return 0;
        }

        $requiredFields = [];
        $completedFields = 0;

        if ($this->isSiswa()) {
            $requiredFields = [
                'nis', 'nisn', 'tempat_lahir', 'tanggal_lahir', 
                'alamat', 'no_telepon', 'kelas_id', 'major', 
                'tahun_ajaran', 'nama_ortu', 'no_telepon_ortu'
            ];
        } elseif ($this->isGuru()) {
            $requiredFields = [
                'nip', 'tempat_lahir', 'tanggal_lahir', 
                'alamat', 'no_telepon', 'email', 
                'mata_pelajaran', 'pendidikan_terakhir'
            ];
        }

        foreach ($requiredFields as $field) {
            if (!empty($profile->$field)) {
                $completedFields++;
            }
        }

        return $requiredFields ? round(($completedFields / count($requiredFields)) * 100) : 100;
    }
}
