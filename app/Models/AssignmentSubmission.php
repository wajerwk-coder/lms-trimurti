<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assignment_submissions';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'siswa_id',
        'file_url',
        'file_path',
        'file_size',
        'submission_text',
        'content',
        'score',
        'feedback',
        'status',
        'graded_by',
        'graded_at',
        'submitted_at',
    ];

    protected $casts = [
        'score'        => 'decimal:2',
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    // Relationships
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Relasi ke siswa (UserCentral).
     * Kolom siswa_id adalah FK ke users_central.id (prioritas).
     * Kolom student_id adalah FK lama ke users (legacy, tetap disimpan).
     * Gunakan siswa_id jika tersedia, fallback ke student_id.
     */
    public function siswa(): BelongsTo
    {
        // siswa_id ada di DB dan FK ke users_central
        return $this->belongsTo(UserCentral::class, 'siswa_id');
    }

    /**
     * Alias student — backward compat via student_id (kolom lama).
     * Hanya pakai jika siswa_id tidak tersedia.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'student_id');
    }

    /**
     * Get siswa yang valid: prioritas siswa_id, fallback student_id
     */
    public function getSiswaAttribute(): ?UserCentral
    {
        if (!empty($this->attributes['siswa_id'])) {
            return $this->siswa()->first();
        }
        return $this->student()->first();
    }

    /**
     * Get nama siswa dari kolom yang tersedia
     */
    public function getNamaSiswaAttribute(): string
    {
        return $this->getAttribute('siswa')?->name ?? '—';
    }

    // Scopes
    public function scopeGraded($query)
    {
        return $query->whereNotNull('score');
    }

    public function scopeUngraded($query)
    {
        return $query->whereNull('score');
    }

    // ✅ Perbaikan: Gunakan assignments.due_date bukan assignments.deadline
    public function scopeLate($query)
    {
        return $query->whereHas('assignment', function($q) {
            $q->whereColumn('assignment_submissions.submitted_at', '>', 'assignments.due_date');
        });
    }

    public function getIsLateAttribute(): bool
    {
        if (!$this->submitted_at || !$this->assignment?->due_date) {
            return false;
        }
        return $this->submitted_at->gt($this->assignment->due_date);
    }

    public function getStatusAttribute(): string
    {
        if (is_null($this->score)) {
            return $this->is_late ? 'late_submission' : 'submitted';
        }
        $maxScore = $this->assignment?->max_score ?? 100;
        return $this->score >= ($maxScore * 0.6) ? 'passed' : 'failed';
    }
}