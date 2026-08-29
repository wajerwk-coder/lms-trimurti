<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'siswa_id',
        'class_subject_id',
        'kelas_id',
        'subject_id',
        'guru_id',
        'date',
        'status',
        'note',
        'created_by',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function siswa()
    {
        // siswa_id menyimpan users_central.id (bukan siswa.id)
        return $this->belongsTo(UserCentral::class, 'siswa_id');
    }

    /**
     * Relasi via student_id (kolom lama)
     */
    public function studentAlt()
    {
        return $this->belongsTo(UserCentral::class, 'student_id');
    }

    /**
     * Ambil nama siswa dari salah satu kolom yang tersedia
     */
    public function getNamaSiswaAttribute(): string
    {
        return $this->siswa?->name
            ?? $this->studentAlt?->name
            ?? '—';
    }

    public function recorder()
    {
        return $this->belongsTo(UserCentral::class, 'recorded_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(UserCentral::class, 'created_by');
    }

    public function guru()
    {
        return $this->belongsTo(UserCentral::class, 'guru_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Alias untuk backward compatibility
    public function student()
    {
        return $this->siswa();
    }

    // Scopes
    public function scopePresent($query)
    {
        return $query->where('status', 'hadir');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'alpha');
    }

    public function scopePermission($query)
    {
        return $query->whereIn('status', ['izin', 'sakit']);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
                    ->whereMonth('date', $month);
    }

    // Accessors
    public function getDurationAttribute()
    {
        if ($this->waktu_masuk && $this->waktu_keluar) {
            return $this->waktu_masuk->diffInMinutes($this->waktu_keluar);
        }

        return null;
    }

    public function getDurationFormattedAttribute()
    {
        $minutes = $this->duration;
        if ($minutes) {
            $hours = floor($minutes / 60);
            $minutes = $minutes % 60;
            return sprintf('%dh %dm', $hours, $minutes);
        }

        return '-';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'hadir' => 'success',
            'izin' => 'info',
            'sakit' => 'warning',
            'alpha' => 'danger',
            default => 'secondary'
        };
    }

    // Methods
    public function isPresent()
    {
        return $this->status === 'hadir';
    }

    public function isAbsent()
    {
        return $this->status === 'alpha';
    }

    public function isPermission()
    {
        return in_array($this->status, ['izin', 'sakit']);
    }
}