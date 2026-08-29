<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemNotification extends Model
{
    protected $table = 'system_notifications_new';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'action_url',
        'is_read',
        'read_at',
        'data',
        'target_role',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(UserCentral::class, 'user_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function getIconAttribute()
    {
        return match($this->type) {
            'exam' => 'fas fa-calendar-check',
            'assignment' => 'fas fa-tasks',
            'material' => 'fas fa-book',
            'practical' => 'fas fa-flask',
            'attendance' => 'fas fa-user-check',
            'announcement' => 'fas fa-bullhorn',
            default => 'fas fa-bell'
        };
    }

    public function getColorAttribute()
    {
        return match($this->type) {
            'exam' => 'danger',
            'assignment' => 'warning',
            'material' => 'info',
            'practical' => 'success',
            'attendance' => 'primary',
            'announcement' => 'secondary',
            default => 'primary'
        };
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
