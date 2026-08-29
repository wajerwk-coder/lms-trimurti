<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Kolom DB: id, guru_id, class_subject_id, title, description, instructions,
 *           due_date, published_at, is_published, is_active, views_count,
 *           submissions_count, subject_id, kelas_id, siswa_id,
 *           created_at, updated_at, deleted_at
 */
class Practical extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'guru_id',
        'class_subject_id',
        'subject_id',
        'kelas_id',
        'siswa_id',
        'title',
        'description',
        'instructions',
        'due_date',
        'published_at',
        'is_published',
        'is_active',
    ];

    protected $casts = [
        'due_date'     => 'datetime',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'is_active'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function guru()
    {
        return $this->belongsTo(UserCentral::class, 'guru_id')->withTrashed();
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /** Nilai praktikum (practical_scores) */
    public function scores()
    {
        return $this->hasMany(NilaiPraktik::class, 'practical_id');
    }

    /** Alias agar kode lama yang pakai PracticalScore juga jalan */
    public function practicalScores()
    {
        return $this->scores();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('due_date', '<', now());
    }

    public function scopeByGuru($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /** Backward compat: beberapa view masih pakai $practical->judul */
    public function getJudulAttribute(): ?string
    {
        return $this->attributes['title'] ?? null;
    }

    /** Backward compat: $practical->tanggal */
    public function getTanggalAttribute()
    {
        return $this->due_date;
    }

    /** Backward compat: $practical->deskripsi */
    public function getDeskripsiAttribute(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_published) return 'draft';
        if ($this->due_date && $this->due_date->isPast()) return 'completed';
        return 'upcoming';
    }

    public function getParticipantCountAttribute(): int
    {
        return $this->scores()->distinct('siswa_id')->count('siswa_id');
    }

    public function getAverageScoreAttribute(): float
    {
        return (float) ($this->scores()->avg('score') ?? 0);
    }

    // ── Methods ───────────────────────────────────────────────────────────────

    public function hasSiswaScore(int $siswaId): bool
    {
        return $this->scores()->where('siswa_id', $siswaId)->exists();
    }
}
