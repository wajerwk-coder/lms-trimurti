<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'academic_periods';

    protected $fillable = [
        'name',
        'academic_year',
        'semester',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /**
     * Kelas yang berada dalam periode ini.
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'academic_period_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Scope: hanya periode yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter berdasarkan tahun ajaran.
     */
    public function scopeByYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Scope: filter berdasarkan semester.
     */
    public function scopeBySemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Ambil periode yang sedang aktif (singleton).
     */
    public static function getActive(): ?static
    {
        return static::where('is_active', true)->latest()->first();
    }

    /**
     * Set periode ini sebagai aktif, nonaktifkan semua yang lain.
     */
    public function setAsActive(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Accessor: label semester yang mudah dibaca.
     */
    public function getSemesterLabelAttribute(): string
    {
        return match ($this->semester) {
            'ganjil' => 'Semester Ganjil',
            'genap'  => 'Semester Genap',
            default  => ucfirst($this->semester),
        };
    }

    /**
     * Accessor: label lengkap "Semester Ganjil 2025/2026".
     */
    public function getFullLabelAttribute(): string
    {
        return $this->semester_label . ' ' . $this->academic_year;
    }

    /**
     * Accessor: status periode (Aktif / Nonaktif).
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    /**
     * Accessor: apakah periode ini sedang berjalan (antara start_date dan end_date).
     */
    public function getIsOngoingAttribute(): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return $this->is_active;
        }
        $today = now()->startOfDay();
        return $today->between($this->start_date, $this->end_date);
    }

    /**
     * Generate nama otomatis dari academic_year + semester.
     */
    public static function generateName(string $academicYear, string $semester): string
    {
        $semLabel = $semester === 'ganjil' ? 'Ganjil' : 'Genap';
        return "Semester {$semLabel} {$academicYear}";
    }
}
