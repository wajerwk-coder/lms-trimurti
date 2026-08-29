<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class PracticeSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'practical_id',
        'teacher_id',
        'title',
        'description',
        'practice_date',
        'start_time',
        'end_time',
        'location',
        'max_participants',
        'status',
        'materials_needed',
        'notes',
        'notification_sent',
        'notification_sent_at',
    ];

    protected $casts = [
        'practice_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'materials_needed' => 'array',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function practical(): BelongsTo
    {
        return $this->belongsTo(Practical::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'teacher_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(UserCentral::class, 'practice_schedule_participants', 'practice_schedule_id', 'student_id')
                    ->withPivot(['status', 'registered_at', 'notes'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('practice_date', '>=', Carbon::today())
                    ->where('status', 'scheduled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('practice_date', Carbon::today());
    }

    public function scopeTomorrow($query)
    {
        return $query->whereDate('practice_date', Carbon::tomorrow());
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Accessors
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'scheduled' => 'primary',
            'ongoing' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'scheduled' => 'Dijadwalkan',
            'ongoing' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui'
        };
    }

    public function getParticipantCountAttribute()
    {
        return $this->participants()->count();
    }

    public function getAvailableSlotsAttribute()
    {
        return $this->max_participants - $this->participant_count;
    }

    // Methods
    public function isUpcoming()
    {
        return $this->practice_date->isFuture() && $this->status === 'scheduled';
    }

    public function canRegister()
    {
        return $this->status === 'scheduled' && 
               $this->practice_date->isFuture() && 
               $this->available_slots > 0;
    }

    public function markNotificationSent()
    {
        $this->update([
            'notification_sent' => true,
            'notification_sent_at' => now(),
        ]);
    }
}
