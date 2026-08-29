<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSchedule extends Model
{
    use SoftDeletes;

    protected $table = 'exam_schedules_new';

    protected $fillable = [
        'title',
        'description',
        'exam_type',
        'subject_id',
        'kelas_id',
        'created_by',
        'start_time',
        'end_time',
        'location',
        'duration_minutes',
        'is_published',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_published' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function creator()
    {
        return $this->belongsTo(UserCentral::class, 'created_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    public function scopeForKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    // Methods
    public function getStatusAttribute()
    {
        if (!$this->is_published) {
            return 'Draft';
        }

        if ($this->start_time > now()) {
            return 'Akan Datang';
        }

        if ($this->end_time > now()) {
            return 'Berlangsung';
        }

        return 'Selesai';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Draft'          => 'secondary',
            'Akan Datang'    => 'warning',
            'Berlangsung'    => 'success',
            'Selesai'        => 'secondary',
            default          => 'secondary',
        };
    }

    public function getDurationFormattedAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . ' jam ' . $minutes . ' menit';
        }
        
        return $minutes . ' menit';
    }
}
