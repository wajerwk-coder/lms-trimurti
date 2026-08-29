<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'guru_id',
        'class_subject_id',
        'subject_id',
        'kelas_id',
        'siswa_id',
        'title',
        'content',
        'file_url',
        'video_url',
        'published_at',
        'views_count',
        'downloads_count',
    ];

    protected $casts = [
        'published_at'    => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    // Relationships
    public function guru()
    {
        return $this->belongsTo(UserCentral::class, 'guru_id')->withTrashed();
    }

    /** Alias — beberapa view pakai $material->teacher */
    public function teacher()
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

    public function downloads()
    {
        return $this->hasMany(MaterialDownload::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeByGuru($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    // Accessors — TIDAK override file_url karena sudah ada di DB
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if (!$bytes) return 'Unknown';
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    // Methods
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
    }
}