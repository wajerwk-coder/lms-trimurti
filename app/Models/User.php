<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUserProfile;
use App\Models\Siswa;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUserProfile;

    protected $table = 'users_central';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'phone',
        'photo',
        'is_active',
        'remember_token',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['photo_url', 'avatar_url', 'age', 'gender_display', 'role_display', 'class_name'];

    // Relationships
    public function siswa(): HasOne
    {
        // FK: siswa.user_id → users_central.id
        return $this->hasOne(Siswa::class, 'user_id');
    }

    public function guru(): HasOne
    {
        // FK: gurus.user_id → users_central.id
        return $this->hasOne(Guru::class, 'user_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'siswa_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class, 'siswa_id');
    }

    public function practicals(): HasMany
    {
        return $this->hasMany(Practical::class, 'guru_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'guru_id');
    }
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    // Scopes untuk filter role
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeGuru($query)
    {
        return $query->where('role', 'guru');
    }

    public function scopeSiswa($query)
    {
        return $query->where('role', 'siswa');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isTeacher(): bool
    {
        return $this->isGuru();
    }

    public function isStudent(): bool
    {
        return $this->isSiswa();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo) {
            return Storage::url($this->photo);
        }

        return asset('images/default-avatar.png');
    }

    public function getAvatarUrlAttribute(): string
    {
        // Use the same logic as photo_url for consistency
        return $this->getPhotoUrlAttribute();
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return now()->diffInYears($this->birth_date);
    }

    public function getGenderDisplayAttribute(): string
    {
        return match($this->gender) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            'laki-laki' => 'Laki-laki',
            'perempuan' => 'Perempuan',
            default => ucfirst($this->gender ?? 'Tidak diketahui')
        };
    }

    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            default => ucfirst($this->role)
        };
    }

    public function getClassNameAttribute(): ?string
    {
        if ($this->isSiswa()) {
            // Get class name through student relation
            if ($this->siswa && $this->siswa->kelas) {
                return $this->siswa->kelas->name;
            }
            // Fallback to direct kelas relation if student relation doesn't exist
            if ($this->kelas) {
                return $this->kelas->name;
            }
        }
        return null;
    }

    public function getKelasIdAttribute(): ?int
    {
        if ($this->isSiswa()) {
            // Get kelas_id through student relation first
            if ($this->siswa) {
                return $this->siswa->kelas_id;
            }
            // Fallback to direct kelas_id
            return $this->attributes['kelas_id'] ?? null;
        }
        return null;
    }

    // Method untuk mendapatkan permissions berdasarkan role
    public function getPermissions(): array
    {
        $permissions = [
            'admin' => [
                'manage_users', 'manage_settings', 'view_reports',
                'manage_classes', 'manage_subjects', 'view_dashboard'
            ],
            'guru' => [
                'manage_materials', 'manage_assignments', 'manage_practicals',
                'grade_assignments', 'grade_practicals', 'view_siswa_progress'
            ],
            'siswa' => [
                'view_materials', 'submit_assignments', 'submit_practicals',
                'view_grades', 'view_attendance', 'view_schedule'
            ]
        ];

        return $permissions[$this->role] ?? [];
    }

    // Method untuk mengecek apakah user memiliki permission tertentu
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    // Method untuk mengecek apakah user memiliki role tertentu
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // Method untuk mengecek apakah user memiliki salah satu dari roles tertentu
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    // Methods moved to HasUserProfile trait

    // Method untuk update profile
    public function updateProfile(array $data): bool
    {
        return $this->update($data);
    }

    // Method untuk change password
    public function changePassword(string $newPassword): bool
    {
        return $this->update(['password' => bcrypt($newPassword)]);
    }

    // Method untuk mengecek apakah user dapat dihapus
    public function canBeDeleted(): bool
    {
        // Cek hanya relationship utama
        $hasRelatedData = $this->attendances()->exists() ||
                         $this->scores()->exists() ||
                         $this->practicals()->exists() ||
                         $this->assignments()->exists();

        return !$hasRelatedData;
    }

    // Method untuk menonaktifkan user
    public function deactivate(): bool
    {
        return $this->update(['status' => 'inactive']);
    }

    // Method untuk mengaktifkan user
    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    // Event handlers
    protected static function boot()
    {
        parent::boot();

        // Auto create username jika tidak disediakan
        static::creating(function ($user) {
            if (empty($user->username)) {
                $user->username = static::generateUsername($user->name);
            }
        });

        // Auto create siswa/guru profile setelah user dibuat
        static::created(function ($user) {
            if ($user->isSiswa()) {
                // Prefer kelas & jurusan chosen during user creation
                $kelasId = $user->kelas_id ?? \App\Models\Kelas::first()?->id;
                $kelas = $kelasId ? \App\Models\Kelas::find($kelasId) : null;

                // Determine major and academic year
                $major = $user->jurusan?->nama
                    ?? $kelas?->major
                    ?? 'Umum';
                $tahunAjaran = $kelas?->academic_year
                    ?? (date('Y') . '/' . (date('Y') + 1));

                // Buat profil siswa di tabel siswa (BUKAN tabel users)
                \App\Models\Siswa::create([
                    'user_id'      => $user->id,
                    'nis'          => 'SIS' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'nisn'         => '000' . str_pad($user->id, 7, '0', STR_PAD_LEFT),
                    'jenis_kelamin'=> $user->gender ?? 'L',
                    'tempat_lahir' => 'Ambon',
                    'tanggal_lahir'=> now()->subYears(18)->format('Y-m-d'),
                    'alamat'       => $user->address ?? 'Alamat tidak tersedia',
                    'no_telepon'   => $user->phone ?? '0000000000',
                    'kelas_id'     => $kelasId ?? \App\Models\Kelas::first()?->id,
                    'major'        => $major,
                    'tahun_ajaran' => $tahunAjaran,
                    'status'       => 'aktif'
                ]);
            } elseif ($user->isGuru()) {
                Guru::create([
                    'user_id'      => $user->id,
                    'nip'          => 'GUR' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'nama'         => $user->name,
                    'name'         => $user->name,
                    'jenis_kelamin'=> $user->gender ?? 'L',
                    'tempat_lahir' => 'Ambon',
                    'tanggal_lahir'=> now()->subYears(30)->format('Y-m-d'),
                    'alamat'       => $user->address ?? 'Alamat tidak tersedia',
                    'no_telepon'   => $user->phone ?? '0000000000',
                    'email'        => $user->email,
                    'mata_pelajaran'   => 'Mata Pelajaran Umum',
                    'pendidikan_terakhir' => 'S1',
                    'status'       => 'aktif'
                ]);
            }
        });

        // Cleanup related data sebelum user dihapus
        static::deleting(function ($user) {
            if ($user->siswa) {
                $user->siswa->delete();
            }
            if ($user->guru) {
                $user->guru->delete();
            }

            // Hapus file photo jika ada
            if ($user->photo && Storage::exists($user->photo)) {
                Storage::delete($user->photo);
            }
        });
    }

    // Helper method untuk generate username
    protected static function generateUsername(string $name): string
    {
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $baseUsername = $username;
        $counter = 1;

        // Cari username yang unik
        while (static::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Relationship dengan Subject (guru)
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'guru_id');
    }
    /**
     * Relationship dengan Material (guru)
     */
    public function materials()
    {
        return $this->hasMany(Material::class, 'guru_id');
    }}
