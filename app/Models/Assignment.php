<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'guru_id',
        'class_subject_id',
        'subject_id',
        'kelas_id',
        'title',
        'description',
        'instructions',
        'file_url',
        'file',
        'file_path',
        'file_size',
        'file_type',
        'due_date',
        'max_score',
        'allow_late',
        'is_published',
    ];

    protected $casts = [
        'due_date'   => 'datetime',
        'max_score'  => 'integer',
        'allow_late' => 'boolean',
        'is_published' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function guru()
    {
        // Pakai withTrashed agar guru yang sudah di-soft-delete masih bisa ditampilkan
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
    
    // Manual relationship for class_subject
    public function getClassSubject()
    {
        return \DB::table('class_subjects')
            ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
            ->join('classes', 'class_subjects.class_id', '=', 'classes.id')
            ->where('class_subjects.id', $this->class_subject_id)
            ->select(
                'class_subjects.id',
                'subjects.name as subject_name',
                'subjects.id as subject_id',
                'classes.name as class_name',
                'classes.id as class_id'
            )
            ->first();
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // ✅ Perbaikan: Gunakan hasManyThrough untuk relationship siswa
    public function siswa()
    {
        return $this->hasManyThrough(
            UserCentral::class,
            AssignmentSubmission::class,
            'assignment_id',
            'id',
            'id',
            'siswa_id'
        );
    }

    // Accessors for backward compatibility
    public function getDeadlineAttribute($value)
    {
        return $this->due_date;
    }

    public function setDeadlineAttribute($value)
    {
        $this->attributes['due_date'] = $value;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActive($query)
    {
        return $query->where('due_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('due_date', '<=', now());
    }

    public function scopeByGuru($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        if (isset($this->attributes['file_url'])) {
            return $this->attributes['file_url'];
        }
        return $this->attributes['file'] ? asset('storage/assignments/' . $this->attributes['file']) : null;
    }

    public function getStatusAttribute()
    {
        if (!$this->is_published) {
            return 'draft';
        }

        if ($this->due_date && $this->due_date->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public function getSubmissionCountAttribute()
    {
        return $this->submissions()->count();
    }

    public function getAverageScoreAttribute()
    {
        return $this->submissions()->whereNotNull('score')->avg('score') ?? 0;
    }

    // Methods
    public function canBeSubmitted()
    {
        return $this->is_published && $this->deadline && $this->deadline->isFuture();
    }

    public function hasUserSubmission($userId)
    {
        return $this->submissions()->where('siswa_id', $userId)->exists();
    }
}