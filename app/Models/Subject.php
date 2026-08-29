<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Guru;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'major_id',
        'jurusan_id',
        'guru_id',
        'kelas_id',
        'type',
        'description',
        'sks',
        'color',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sks'       => 'integer',
        'order'     => 'integer',
    ];

    // ── Backward-compat accessors / mutators ──────────────────────────────

    public function getNamaAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function setNamaAttribute($value): void
    {
        $this->attributes['name'] = $value;
    }

    // ── Relationships ─────────────────────────────────────────────────────

    /**
     * Relasi ke Jurusan via jurusan_id (FK baru di DB subjects).
     * DB subjects punya jurusan_id DAN major_id — keduanya → jurusans.id.
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * Relasi via major_id — backward compat untuk kode lama pakai $subject->major
     */
    public function major()
    {
        return $this->belongsTo(Jurusan::class, 'major_id');
    }

    /**
     * Guru utama pengampu (FK tunggal subjects.guru_id) — backward compat
     */
    public function guru()
    {
        return $this->belongsTo(UserCentral::class, 'guru_id');
    }

    /**
     * Semua guru yang mengajar mata pelajaran ini (many-to-many via pivot guru_subjects).
     * guru_subjects.guru_id → gurus.id
     */
    public function gurus()
    {
        return $this->belongsToMany(Guru::class, 'guru_subjects', 'subject_id', 'guru_id')
                    ->withTimestamps();
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'subject_id');
    }

    public function practicals()
    {
        return $this->hasMany(Practical::class, 'subject_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'subject_id');
    }

    public function examSchedules()
    {
        return $this->hasMany(ExamSchedule::class, 'subject_id');
    }
}
