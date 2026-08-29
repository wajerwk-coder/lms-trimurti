<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurusan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jurusans';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    /**
     * Accessor 'nama' → alias ke 'name' untuk backward compatibility
     */
    public function getNamaAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Accessor 'kode' → alias ke 'code' untuk backward compatibility
     */
    public function getKodeAttribute(): ?string
    {
        return $this->attributes['code'] ?? null;
    }

    /**
     * Accessor 'deskripsi' → alias ke 'description' untuk backward compatibility
     */
    public function getDeskripsiAttribute(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    protected $casts = [
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship dengan model Kelas (FK: jurusan_id di tabel classes)
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'jurusan_id');
    }

    /**
     * Relationship dengan model Siswa (via Kelas).
     * Menggunakan hasManyThrough: Jurusan → Kelas (jurusan_id) → Siswa (kelas_id)
     */
    public function siswa(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Siswa::class,  // target
            Kelas::class,              // through
            'jurusan_id',              // FK di classes → jurusans
            'kelas_id',                // FK di siswa → classes
            'id',                      // local key di jurusans
            'id'                       // local key di classes
        );
    }

    /**
     * Get total siswa dalam jurusan
     */
    public function getTotalSiswaAttribute()
    {
        return $this->siswa()->count();
    }

    /**
     * Get total kelas dalam jurusan
     */
    public function getTotalKelasAttribute()
    {
        return $this->kelas()->count();
    }

    /**
     * Scope untuk jurusan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get mata pelajaran as formatted string
     */
    public function getMataPelajaranStringAttribute()
    {
        if (is_array($this->mata_pelajaran)) {
            return implode(', ', $this->mata_pelajaran);
        }
        return '';
    }

    /**
     * Static method untuk get jurusan kesehatan default
     */
    public static function getDefaultJurusan()
    {
        return [
            [
                'nama' => 'Keperawatan',
                'kode' => 'KPR',
                'deskripsi' => 'Program Keahlian Keperawatan',
                'mata_pelajaran' => [
                    'Anatomi Fisiologi',
                    'Patologi',
                    'Farmakologi',
                    'Keperawatan Dasar',
                    'Keperawatan Medikal Bedah',
                    'Keperawatan Anak',
                    'Keperawatan Maternitas',
                    'Keperawatan Jiwa',
                    'Keperawatan Komunitas'
                ]
            ],
            [
                'nama' => 'Farmasi',
                'kode' => 'FAR',
                'deskripsi' => 'Program Keahlian Farmasi Klinis dan Komunitas',
                'mata_pelajaran' => [
                    'Kimia Farmasi',
                    'Farmakologi',
                    'Farmasetika',
                    'Farmakognosi',
                    'Farmasi Klinik',
                    'Managemen Farmasi',
                    'Kimia Analisis',
                    'Biologi Farmasi'
                ]
            ],
            [
                'nama' => 'Teknologi Laboratorium Medik',
                'kode' => 'TLM',
                'deskripsi' => 'Program Keahlian Analis Kesehatan',
                'mata_pelajaran' => [
                    'Hematologi',
                    'Kimia Klinik',
                    'Mikrobiologi',
                    'Parasitologi',
                    'Imunologi',
                    'Urinalisis',
                    'Histopatologi',
                    'Toksikologi'
                ]
            ]
        ];
    }

    /**
     * Seed default jurusan
     */
    public static function seedDefault()
    {
        $defaultJurusan = self::getDefaultJurusan();
        
        foreach ($defaultJurusan as $jurusan) {
            self::updateOrCreate(
                ['kode' => $jurusan['kode']],
                $jurusan
            );
        }
    }
}