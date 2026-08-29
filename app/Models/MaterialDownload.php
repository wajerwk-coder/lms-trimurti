<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialDownload extends Model
{
    use HasFactory;

    // Disable Laravel timestamps since we only have downloaded_at
    public $timestamps = false;

    protected $fillable = [
        'material_id',
        'siswa_id',
        'downloaded_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    // Relationships
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function siswa()
    {
        return $this->belongsTo(UserCentral::class, 'siswa_id');
    }

    // Scopes
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('downloaded_at', '>=', now()->subDays($days));
    }

    public function scopeByMaterial($query, $materialId)
    {
        return $query->where('material_id', $materialId);
    }

    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    // Methods
    public function logDownload($ipAddress = null, $userAgent = null)
    {
        $this->update([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'downloaded_at' => now(),
        ]);
    }

    // Accessor
    public function getDownloadedAtFormattedAttribute()
    {
        return $this->downloaded_at->format('d M Y H:i');
    }
}