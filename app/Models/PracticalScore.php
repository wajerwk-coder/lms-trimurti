<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticalScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'practical_id',
        'siswa_id',
        'criteria_id',
        'score',
        'feedback',
        'laporan_generated',
        'laporan_generated_at',
    ];

    protected $casts = [
        'score' => 'float',
        'laporan_generated' => 'boolean',
        'laporan_generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function practical()
    {
        return $this->belongsTo(Practical::class);
    }

    public function siswa()
    {
        return $this->belongsTo(UserCentral::class, 'siswa_id');
    }

    public function criteria()
    {
        return $this->belongsTo(KriteriaPenilaian::class, 'criteria_id');
    }

    // Scopes
    public function scopeWithFeedback($query)
    {
        return $query->whereNotNull('feedback');
    }

    public function scopeHighScores($query, $threshold = 80)
    {
        return $query->where('score', '>=', $threshold);
    }

    public function scopeLowScores($query, $threshold = 60)
    {
        return $query->where('score', '<', $threshold);
    }

    public function scopeReportGenerated($query)
    {
        return $query->where('laporan_generated', true);
    }

    public function scopeReportNotGenerated($query)
    {
        return $query->where('laporan_generated', false);
    }

    // Accessors
    public function getGradeAttribute()
    {
        if ($this->score >= 85) return 'A';
        if ($this->score >= 75) return 'B';
        if ($this->score >= 65) return 'C';
        if ($this->score >= 55) return 'D';
        return 'E';
    }

    public function getGradeColorAttribute()
    {
        return match($this->grade) {
            'A' => 'success',
            'B' => 'primary',
            'C' => 'warning',
            'D' => 'orange',
            'E' => 'danger',
            default => 'secondary'
        };
    }

    public function getIsPassedAttribute()
    {
        return $this->score >= 65;
    }

    public function getScorePercentageAttribute()
    {
        $maxScore = $this->criteria?->max_score ?? 100;
        return ($this->score / $maxScore) * 100;
    }

    // Methods
    public function markAsReportGenerated()
    {
        $this->update([
            'laporan_generated' => true,
            'laporan_generated_at' => now(),
        ]);
    }

    public function isReportGenerated()
    {
        return $this->laporan_generated;
    }
}