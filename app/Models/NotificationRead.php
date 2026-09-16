<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Status baca notifikasi per-user.
 *
 * Menyimpan record (user_id, notification_id) agar setiap user
 * punya status baca tersendiri, tidak bergantung pada kolom read_at
 * di tabel notifications yang di-share semua user.
 */
class NotificationRead extends Model
{
    protected $table = 'notification_reads';

    protected $fillable = [
        'user_id',
        'notification_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'user_id');
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
}
