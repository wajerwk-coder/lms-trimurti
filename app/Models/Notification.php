<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengirim_id', 'sender_id',
        'penerima_id', 'receiver_id',
        'tipe_penerima', 'receiver_type',
        'tipe', 'type',
        'judul', 'title',
        'pesan', 'message',
        'url_aksi', 'action_url',
        'prioritas', 'priority',
        'status',
        'read_at',
        'scheduled_at',
        'data'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function sender(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'pengirim_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'penerima_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('prioritas', ['tinggi', 'darurat']);
    }

    // Accessors
    public function getIsUnreadAttribute(): bool
    {
        return is_null($this->read_at);
    }

    public function getIconAttribute(): string
    {
        return match($this->tipe) {
            'info' => 'ℹ️',
            'peringatan' => '⚠️',
            'sukses' => '✅',
            'error' => '❌',
            'sistem' => '⚙️',
            default => '📧'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->prioritas) {
            'rendah' => 'blue',
            'sedang' => 'green',
            'tinggi' => 'orange',
            'darurat' => 'red',
            default => 'gray'
        };
    }

    // Accessors untuk kompatibilitas dengan view
    public function getTitleAttribute()
    {
        return $this->judul;
    }

    public function getMessageAttribute()
    {
        return $this->pesan;
    }

    public function getActionUrlAttribute()
    {
        return $this->url_aksi;
    }

    public function getTypeAttribute()
    {
        return $this->tipe;
    }

    public function getPriorityAttribute()
    {
        return $this->prioritas;
    }

    // Methods
    public function markAsRead(): void
    {
        $this->update([
            'read_at' => now()
        ]);
    }

    public function markAsUnread(): void
    {
        $this->update([
            'read_at' => null
        ]);
    }

    public function getIsScheduledAttribute(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')->where('scheduled_at', '>', now());
    }

    public function scopeReadyToSend($query)
    {
        return $query->where(function($q) {
            $q->whereNull('scheduled_at')
              ->orWhere('scheduled_at', '<=', now());
        });
    }
    
    // Event handlers to sync English and Indonesian columns
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($notification) {
            // Sync Indonesian to English columns
            if ($notification->pengirim_id && !$notification->sender_id) {
                $notification->sender_id = $notification->pengirim_id;
            }
            if ($notification->penerima_id && !$notification->receiver_id) {
                $notification->receiver_id = $notification->penerima_id;
            }
            if ($notification->tipe_penerima && !$notification->receiver_type) {
                $notification->receiver_type = $notification->tipe_penerima === 'semua' ? 'all' : $notification->tipe_penerima;
            }
            if ($notification->tipe && !$notification->type) {
                $typeMap = ['peringatan' => 'warning', 'sukses' => 'success', 'error' => 'danger', 'sistem' => 'system'];
                $notification->type = $typeMap[$notification->tipe] ?? $notification->tipe;
            }
            if ($notification->judul) {
                $notification->title = $notification->judul;
            } elseif (!$notification->title) {
                $notification->title = 'Notification';
            }
            if ($notification->pesan) {
                $notification->message = $notification->pesan;
            } elseif (!$notification->message) {
                $notification->message = '';
            }
            if ($notification->url_aksi && !$notification->action_url) {
                $notification->action_url = $notification->url_aksi;
            }
            if ($notification->prioritas && !$notification->priority) {
                $priorityMap = ['rendah' => 'low', 'sedang' => 'medium', 'tinggi' => 'high', 'darurat' => 'urgent'];
                $notification->priority = $priorityMap[$notification->prioritas] ?? $notification->prioritas;
            }
            
            // Handle status mapping
            if ($notification->status) {
                $statusMap = ['belum_dibaca' => 'unread', 'terbaca' => 'read', 'diarsipkan' => 'archived'];
                $englishStatus = $statusMap[$notification->status] ?? $notification->status;
                $notification->setAttribute('status', $englishStatus);
            }
            
            // Sync English to Indonesian columns if needed
            if (!$notification->judul && $notification->title) {
                $notification->judul = $notification->title;
            }
            if (!$notification->pesan && $notification->message) {
                $notification->pesan = $notification->message;
            }
        });
        
        static::updating(function ($notification) {
            // Same sync logic for updates
            if ($notification->isDirty('judul') && !$notification->isDirty('title')) {
                $notification->title = $notification->judul;
            }
            if ($notification->isDirty('pesan') && !$notification->isDirty('message')) {
                $notification->message = $notification->pesan;
            }
            if ($notification->isDirty('title') && !$notification->isDirty('judul')) {
                $notification->judul = $notification->title;
            }
            if ($notification->isDirty('message') && !$notification->isDirty('pesan')) {
                $notification->pesan = $notification->message;
            }
        });
    }
}