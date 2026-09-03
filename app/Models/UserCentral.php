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
            // Coba URL storage langsung — di Railway pakai full URL
            $url = asset('storage/' . $this->photo);
            return $url;
        }
        // Fallback: generate avatar otomatis berdasarkan nama
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
    public function adminProfile(): HasOne
    {
        // Jika tabel admins belum ada, skip
        return $this->hasOne(Guru::class, 'user_id')->where('role', 'admin');
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
}