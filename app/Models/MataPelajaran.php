<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MataPelajaran extends Model
{
    use SoftDeletes;

    protected $table = 'subjects';

    protected $fillable = [
        'name',
        'code',
        'major_id',
        'description',
        'guru_id',
        'kelas_id',
        'sks',
        'type',
        'color',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sks'       => 'integer',
        'order'     => 'integer',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'major_id');
    }

    public function guru()
    {
        return $this->belongsTo(UserCentral::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'subject_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'subject_id');
    }

    public function practicals()
    {
        return $this->hasMany(Practical::class, 'subject_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTeori($query)
    {
        return $query->where('type', 'teori');
    }

    public function scopePraktikum($query)
    {
        return $query->where('type', 'praktikum');
    }

    public function scopeCampuran($query)
    {
        return $query->where('type', 'campuran');
    }

    /**
     * Umum = teori + campuran (bukan murni kejuruan/praktikum)
     */
    public function scopeUmum($query)
    {
        return $query->whereIn('type', ['teori', 'campuran']);
    }

    /**
     * Kejuruan = praktikum + campuran (bukan murni teori)
     */
    public function scopeKejuruan($query)
    {
        return $query->whereIn('type', ['praktikum', 'campuran']);
    }
}
