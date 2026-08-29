<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk tabel `practical_scores`.
 */
class NilaiPraktik extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'practical_scores';

    protected $fillable = [
        'practical_id',
        'siswa_id',
        'criteria_id',
        'score',
        'feedback',
        'graded_by',
        'guru_id',
        'graded_at',
    ];

    protected $casts = [
        'score'      => 'decimal:2',
        'graded_at'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function practical(): BelongsTo
    {
        return $this->belongsTo(Practical::class, 'practical_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'siswa_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'guru_id');
    }

    /**
     * Relasi ke KriteriaPenilaian (tabel assessment_criteria).
     * Kolom criteria_id ada di DB practical_scores.
     */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(KriteriaPenilaian::class, 'criteria_id');
    }

    /** Alias Bahasa Indonesia */
    public function kriteria(): BelongsTo
    {
        return $this->criteria();
    }

    /**
     * gradedBy — siapa yang memberi nilai (bisa guru_id atau graded_by).
     * DB punya kolom graded_by dan guru_id — keduanya ada.
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'graded_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeBySiswa($query, int $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    public function scopeByGuru($query, int $guruId)
    {
        // DB punya dua kolom: guru_id dan graded_by — keduanya bisa berisi guru
        return $query->where(function ($q) use ($guruId) {
            $q->where('guru_id', $guruId)
              ->orWhere('graded_by', $guruId);
        });
    }

    public function scopeByPractical($query, int $practicalId)
    {
        return $query->where('practical_id', $practicalId);
    }

    /** Backward compat — dulu ada status 'final', sekarang semua dianggap final */
    public function scopeFinal($query)
    {
        return $query->whereNotNull('score');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getGradeAttribute(): string
    {
        $s = (float) ($this->score ?? 0);
        if ($s >= 90) return 'A';
        if ($s >= 80) return 'B';
        if ($s >= 70) return 'C';
        if ($s >= 60) return 'D';
        return 'E';
    }

    public function getGradeColorAttribute(): string
    {
        return match ($this->grade) {
            'A' => 'success',
            'B' => 'primary',
            'C' => 'info',
            'D' => 'warning',
            default => 'danger',
        };
    }

    public function getCheckedSopAttribute(): array
    {
        if (empty($this->feedback)) return [];
        $decoded = json_decode($this->feedback, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function isPassing(): bool
    {
        return (float) ($this->score ?? 0) >= 70;
    }
}
