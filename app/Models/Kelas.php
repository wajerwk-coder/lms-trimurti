<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Siswa;

class Kelas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'grade',
        'major_id',
        'jurusan_id',
        'academic_year',
        'status',
        'wallpaper',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Add accessor for backward compatibility
    public function getNamaAttribute()
    {
        return $this->name;
    }

    /**
     * Relasi ke Jurusan.
     * DB classes punya dua kolom: jurusan_id (baru) dan major_id (lama).
     * Keduanya menyimpan jurusans.id.
     * Gunakan jurusan_id sebagai FK utama, dengan fallback ke major_id via scope.
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * Fallback alias via major_id — untuk backward compat query lama.
     * Jika jurusan_id null (data sangat lama), coba via major_id.
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'major_id');
    }

    /**
     * Accessor: ambil jurusan dari jurusan_id, fallback ke major_id
     */
    public function getJurusanEagerAttribute(): ?Jurusan
    {
        return $this->jurusan ?? $this->major;
    }

    /**
     * Siswa yang terdaftar di kelas ini (via tabel siswa)
     */
    public function students(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    /**
     * Alias Indonesian untuk students()
     */
    public function siswa(): HasMany
    {
        return $this->students();
    }

    /**
     * User accounts siswa di kelas ini (via relasi Siswa)
     */
    public function users(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    /**
     * Scope untuk kelas aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk kelas berdasarkan grade
     */
    public function scopeByGrade($query, $grade)
    {
        // Jika ada kolom grade, filter by it; otherwise return all
        try {
            return $query->where('grade', $grade);
        } catch (\Exception $e) {
            return $query;
        }
    }

    /**
     * Scope untuk kelas berdasarkan jurusan
     */
    public function scopeByMajor($query, $major)
    {
        return $query->where('major_id', $major);
    }

    /**
     * Subjects / mata pelajaran di kelas ini via class_subjects
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id');
    }
}
