<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Subject;

class Guru extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'gurus';

    protected $fillable = [
        'user_id',
        'nip',
        // Kolom yang ADA di DB gurus (struktur aktual):
        'name',                 // NOT NULL
        'email',                // NOT NULL
        'password',             // nullable
        'phone',                // nullable (alias no_telepon)
        'jenis_kelamin',        // nullable
        'tempat_lahir',         // nullable
        'tanggal_lahir',        // nullable date
        'address',              // nullable (kolom alamat di DB)
        'mata_pelajaran',       // nullable
        'pendidikan_terakhir',  // nullable
        'email_pribadi',        // nullable
        'jurusan_pendidikan',   // nullable
        'tahun_mulai_kerja',    // nullable
        'agama',                // nullable
        'foto',                 // nullable
        'status',               // NOT NULL default 'aktif'
        'is_active',            // NOT NULL default 1
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tanggal_lahir'    => 'date',
        'tahun_mulai_kerja'=> 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    protected $appends = ['photo_url', 'age', 'gender_display', 'work_duration'];

    // Accessors
    public function getNameAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function getNamaAttribute(): string
    {
        // Alias 'nama' → kolom 'name' di DB
        return $this->attributes['name'] ?? '';
    }

    public function getAlamatAttribute(): string
    {
        // Alias 'alamat' → kolom 'address' di DB
        return $this->attributes['address'] ?? '';
    }

    public function getNoTeleponAttribute(): string
    {
        // Alias 'no_telepon' → kolom 'phone' di DB
        return $this->attributes['phone'] ?? '';
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function getAgeAttribute()
    {
        return $this->date_lahir ? now()->diffInYears($this->date_lahir) : null;
    }

    public function getGenderDisplayAttribute()
    {
        return match($this->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => $this->jenis_kelamin
        };
    }

    public function getWorkDurationAttribute()
    {
        return $this->tahun_mulai_kerja ? now()->year - $this->tahun_mulai_kerja : null;
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

    public function scopeBySubject($query, $subject)
    {
        return $query->where('mata_pelajaran', 'like', '%' . $subject . '%');
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'aktif';
    }

    public function getRoleAttribute()
    {
        return 'guru';
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(UserCentral::class, 'user_id');
    }

    /**
     * Many-to-many: guru mengajar banyak mata pelajaran.
     * Pivot: guru_subjects (guru_id → gurus.id, subject_id → subjects.id)
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'guru_subjects', 'guru_id', 'subject_id')
                    ->withTimestamps();
    }

    /**
     * Alias Bahasa Indonesia
     */
    public function mataPelajarans()
    {
        return $this->subjects();
    }

    /**
     * Helper: nama-nama mapel sebagai array
     */
    public function getSubjectNamesAttribute(): array
    {
        return $this->subjects->pluck('name')->toArray();
    }

    /**
     * Helper: nama-nama mapel sebagai string
     */
    public function getSubjectNamesStringAttribute(): string
    {
        return $this->subjects->pluck('name')->implode(', ') ?: ($this->mata_pelajaran ?? '—');
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'guru_id', 'user_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'guru_id', 'user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'guru_id', 'user_id');
    }

    public function practicals()
    {
        return $this->hasMany(Practical::class, 'guru_id', 'user_id');
    }

    public function scores()
    {
        return $this->hasMany(NilaiPraktik::class, 'guru_id', 'user_id');
    }
}
