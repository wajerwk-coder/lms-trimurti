<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'guru_id',
        'practical_id',
        'practice_module_id',
        'score',
        'theory_score',
        'practice_score',
        'attitude_score',
        'scored_at',
        'notes'
    ];

    protected $casts = [
        'score' => 'float',
        'theory_score' => 'float',
        'practice_score' => 'float',
        'attitude_score' => 'float',
        'scored_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the siswa that owns the score.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'siswa_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'guru_id');
    }

    /**
     * Get the practical that owns the score.
     */
    public function practical(): BelongsTo
    {
        return $this->belongsTo(Practical::class);
    }

    /**
     * Get the practice module that owns the score.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(PracticeModule::class, 'practice_module_id');
    }

    /**
     * Get the competency indicators for the score.
     */
    public function competencyIndicators(): HasMany
    {
        return $this->hasMany(CompetencyIndicator::class);
    }

    /**
     * Scope a query to only include scores with passing grade.
     */
    public function scopePassed($query)
    {
        return $query->where('score', '>=', 75);
    }

    /**
     * Scope a query to only include scores with failing grade.
     */
    public function scopeFailed($query)
    {
        return $query->where('score', '<', 75);
    }

    /**
     * Get the grade letter for the score.
     */
    public function getGradeAttribute(): string
    {
        if ($this->score >= 90) return 'A';
        if ($this->score >= 80) return 'B';
        if ($this->score >= 70) return 'C';
        if ($this->score >= 60) return 'D';
        return 'E';
    }

    /**
     * Check if the score is passing.
     */
    public function getIsPassingAttribute(): bool
    {
        return $this->score >= 75;
    }

    /**
     * Get total weighted score from competency indicators.
     */
    public function getTotalWeightedScoreAttribute(): float
    {
        return $this->competencyIndicators->sum(function($indicator) {
            return $indicator->weighted_score ?? 0;
        });
    }

    /**
     * Get completion status based on practical date.
     */
    public function getCompletionStatusAttribute(): string
    {
        if (!$this->practical) return 'not_started';

        return $this->scored_at ? 'completed' : 'in_progress';
    }
}