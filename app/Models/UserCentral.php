<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCentral extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users_central';

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role',
        'phone',
        'photo',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['photo_url', 'role_display'];

    // Accessors
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            // URL Cloudinary — pakai langsung tanpa transformasi
            if (str_starts_with($this->photo, 'https://res.cloudinary.com')) {
                return $this->photo;
            }
            // URL http lainnya, pakai langsung
            if (str_starts_with($this->photo, 'http')) {
                return $this->photo;
            }
            // Path lokal (storage)
            return asset('storage/' . $this->photo);
        }
        // Fallback: avatar inisial
        $initials = urlencode($this->name ?? 'User');
        $colors = [
            'admin' => ['bg' => '3b82f6', 'color' => 'fff'],
            'guru'  => ['bg' => '0f766e', 'color' => 'fff'],
            'siswa' => ['bg' => '7c3aed', 'color' => 'fff'],
        ];
        $c = $colors[$this->role ?? ''] ?? ['bg' => '6366f1', 'color' => 'fff'];
        return "https://ui-avatars.com/api/?name={$initials}&background={$c['bg']}&color={$c['color']}&size=128&bold=true";
    }

    public function getRoleDisplayAttribute()
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            default => $this->role
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

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

    // Methods
    public function isActive()
    {
        return $this->is_active;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isGuru()
    {
        return $this->role === 'guru';
    }

    public function isSiswa()
    {
        return $this->role === 'siswa';
    }

    // Relationships
    /**
     * Profil admin — saat ini sistem tidak punya tabel admins terpisah,
     * admin hanya diidentifikasi dari role di users_central.
     * Method ini tidak dipakai secara aktif; tersedia untuk backward compat.
     */
    public function adminProfile(): HasOne
    {
        // Admin tidak punya tabel profil sendiri — kembalikan null-safe hasOne ke diri sendiri
        // dengan kondisi yang tidak akan pernah match agar tidak error
        return $this->hasOne(static::class, 'id')->whereRaw('0=1');
    }

    public function guruProfile(): HasOne
    {
        return $this->hasOne(Guru::class, 'user_id');
    }

    public function siswaProfile(): HasOne
    {
        // Tabel siswa → FK user_id
        return $this->hasOne(\App\Models\Siswa::class, 'user_id');
    }

    // Alias yang lebih pendek
    public function siswa(): HasOne
    {
        return $this->siswaProfile();
    }

    public function guru(): HasOne
    {
        return $this->guruProfile();
    }

    // Relasi konten guru
    public function materials()
    {
        return $this->hasMany(\App\Models\Material::class, 'guru_id');
    }

    public function assignments()
    {
        return $this->hasMany(\App\Models\Assignment::class, 'guru_id');
    }

    public function practicals()
    {
        return $this->hasMany(\App\Models\Practical::class, 'guru_id');
    }

    // Relasi absensi siswa
    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class, 'siswa_id');
    }

    // Submission tugas siswa
    public function assignmentSubmissions()
    {
        return $this->hasMany(\App\Models\AssignmentSubmission::class, 'siswa_id');
    }

    // Nilai praktikum siswa
    public function practicalScores()
    {
        return $this->hasMany(\App\Models\NilaiPraktik::class, 'siswa_id');
    }

    // Notifikasi yang diterima
    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class, 'penerima_id');
    }

    public function getProfileAttribute()
    {
        return match($this->role) {
            'guru' => $this->guruProfile,
            'siswa' => $this->siswaProfile,
            default => null
        };
    }

    // Shortcut untuk mendapatkan kelas_id siswa
    public function getKelasIdAttribute()
    {
        if ($this->isSiswa() && $this->siswaProfile) {
            return $this->siswaProfile->kelas_id;
        }
        return null;
    }

    // ── Helper methods (compat dengan User model) ─────────────────────────

    public function isTeacher(): bool { return $this->isGuru(); }
    public function isStudent(): bool { return $this->isSiswa(); }

    public function hasRole(string $role): bool { return $this->role === $role; }
    public function hasAnyRole(array $roles): bool { return in_array($this->role, $roles); }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    public function getPermissions(): array
    {
        return match($this->role) {
            'admin'  => ['manage_users', 'manage_settings', 'view_reports', 'manage_classes', 'manage_subjects', 'view_dashboard'],
            'guru'   => ['manage_materials', 'manage_assignments', 'manage_practicals', 'grade_assignments', 'grade_practicals', 'view_siswa_progress'],
            'siswa'  => ['view_materials', 'submit_assignments', 'submit_practicals', 'view_grades', 'view_attendance', 'view_schedule'],
            default  => [],
        };
    }

    public function updateProfile(array $data): bool { return $this->update($data); }

    public function changePassword(string $newPassword): bool
    {
        return $this->update(['password' => bcrypt($newPassword)]);
    }

    public function canBeDeleted(): bool
    {
        return !($this->attendances()->exists()
            || $this->practicals()->exists()
            || $this->assignments()->exists());
    }

    public function deactivate(): bool { return $this->update(['is_active' => false]); }
    public function activate(): bool   { return $this->update(['is_active' => true]); }

    public function getAvatarUrlAttribute(): string { return $this->photo_url; }

    public function getClassNameAttribute(): ?string
    {
        return $this->isSiswa() ? ($this->siswa?->kelas?->name ?? null) : null;
    }

    // ── Boot hooks ────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate username jika kosong
        static::creating(function ($user) {
            if (empty($user->username)) {
                $user->username = static::generateUsername($user->name);
            }
        });

        // Auto-create profil siswa/guru setelah user dibuat
        static::created(function ($user) {
            if ($user->isSiswa()) {
                $kelasId     = \App\Models\Kelas::first()?->id;
                $kelas       = $kelasId ? \App\Models\Kelas::find($kelasId) : null;
                $tahunAjaran = $kelas?->academic_year ?? (date('Y') . '/' . (date('Y') + 1));
                $semester    = $kelas?->semester ?? \App\Models\AcademicPeriod::getActive()?->semester ?? 'ganjil';

                \App\Models\Siswa::firstOrCreate(['user_id' => $user->id], [
                    'nis'           => 'SIS' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'nisn'          => '000' . str_pad($user->id, 7, '0', STR_PAD_LEFT),
                    'jenis_kelamin' => 'L',
                    'kelas_id'      => $kelasId,
                    'tahun_ajaran'  => $tahunAjaran,
                    'semester'      => $semester,
                    'status'        => 'aktif',
                ]);
            } elseif ($user->isGuru()) {
                \App\Models\Guru::firstOrCreate(['user_id' => $user->id], [
                    'nip'    => 'GUR' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'status' => 'aktif',
                ]);
            }
        });

        // Cascade soft-delete ke profil siswa/guru
        static::deleting(function ($user) {
            $user->siswa?->delete();
            $user->guru?->delete();
        });
    }

    public static function generateUsername(string $name): string
    {
        $base    = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $username = $base;
        $counter  = 1;
        while (static::where('username', $username)->exists()) {
            $username = $base . $counter++;
        }
        return $username;
    }
}