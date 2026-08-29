<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KriteriaPenilaian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assessment_criteria';

    protected $fillable = [
        'name',
        'description',
        'weight',
        'max_score',
        'subject_id',
        'code',
        'is_active',
        'type',
        'kategori',
        'mata_praktik',
        'tingkat_kelas',
        'sop_checklist',
    ];

    protected $casts = [
        'weight'        => 'integer',
        'max_score'     => 'integer',
        'is_active'     => 'boolean',
        'sop_checklist' => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
        'weight'    => 10,
        'max_score' => 100,
    ];

    // ── Kategori constants ─────────────────────────────────────────────────

    const KATEGORI_PERSIAPAN   = 'persiapan';
    const KATEGORI_PELAKSANAAN = 'pelaksanaan';
    const KATEGORI_HASIL       = 'hasil';
    const KATEGORI_SIKAP       = 'sikap';

    const TINGKAT_X   = 'X';
    const TINGKAT_XI  = 'XI';
    const TINGKAT_XII = 'XII';

    // ── Backward-compat accessors / mutators ───────────────────────────────

    public function getNamaAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function setNamaAttribute($value): void
    {
        $this->attributes['name'] = $value;
    }

    public function getDeskripsiAttribute(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    public function setDeskripsiAttribute($value): void
    {
        $this->attributes['description'] = $value;
    }

    /** bobot → weight (integer %) */
    public function getBobotAttribute(): int
    {
        return (int) ($this->attributes['weight'] ?? 0);
    }

    public function setBobotAttribute($value): void
    {
        $this->attributes['weight'] = (int) $value;
    }

    public function getStatusAttribute(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? true);
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['is_active'] = (bool) $value;
    }

    // ── Computed attributes ────────────────────────────────────────────────

    /** Bobot dalam format "20%" */
    public function getBobotPersenAttribute(): string
    {
        return $this->bobot . '%';
    }

    /** Jumlah item SOP checklist */
    public function getJumlahChecklistAttribute(): int
    {
        return is_array($this->sop_checklist) ? count($this->sop_checklist) : 0;
    }

    /** Label kategori dalam Bahasa Indonesia */
    public function getKategoriLabelAttribute(): string
    {
        return self::getKategoriList()[$this->kategori] ?? ucfirst($this->kategori ?? '');
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function nilaiPraktik(): HasMany
    {
        // Tabel detail_penilaian mungkin belum ada; diakses hanya setelah cek Schema
        return $this->hasMany(DetailPenilaian::class, 'kriteria_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeByMataPraktik($query, string $mataPraktik)
    {
        return $query->where('mata_praktik', $mataPraktik);
    }

    public function scopeByTingkatKelas($query, string $tingkat)
    {
        return $query->where('tingkat_kelas', $tingkat);
    }

    // ── Static helpers ─────────────────────────────────────────────────────

    public static function getKategoriList(): array
    {
        return [
            self::KATEGORI_PERSIAPAN   => 'Persiapan',
            self::KATEGORI_PELAKSANAAN => 'Pelaksanaan',
            self::KATEGORI_HASIL       => 'Hasil',
            self::KATEGORI_SIKAP       => 'Sikap Profesional',
        ];
    }

    public static function getTingkatKelasList(): array
    {
        return [
            self::TINGKAT_X   => 'Kelas X',
            self::TINGKAT_XI  => 'Kelas XI',
            self::TINGKAT_XII => 'Kelas XII',
        ];
    }

    // ── Seed defaults ──────────────────────────────────────────────────────

    public static function seedDefault(): void
    {
        $all = array_merge(
            self::getDefaultKriteriaKeperawatan(),
            self::getDefaultKriteriaFarmasi()
        );

        foreach ($all as $k) {
            self::updateOrCreate(
                [
                    'name'          => $k['name'],
                    'mata_praktik'  => $k['mata_praktik'],
                    'tingkat_kelas' => $k['tingkat_kelas'],
                ],
                [
                    'kategori'      => $k['kategori'],
                    'description'   => $k['description'] ?? null,
                    'weight'        => $k['weight'],
                    'max_score'     => $k['max_score'] ?? 100,
                    'is_active'     => true,
                    'sop_checklist' => $k['sop_checklist'] ?? [],
                ]
            );
        }
    }

    public static function getDefaultKriteriaKeperawatan(): array
    {
        return [
            [
                'name'          => 'Persiapan Alat dan Bahan',
                'kategori'      => self::KATEGORI_PERSIAPAN,
                'weight'        => 20,
                'description'   => 'Kelengkapan dan kesesuaian alat serta bahan yang disiapkan',
                'mata_praktik'  => 'Keperawatan Dasar',
                'tingkat_kelas' => self::TINGKAT_X,
                'sop_checklist' => [
                    'Menyiapkan alat sesuai prosedur',
                    'Memeriksa kelengkapan alat',
                    'Memastikan sterilitas alat',
                    'Menyiapkan bahan habis pakai',
                    'Mengatur posisi alat dengan ergonomis',
                ],
            ],
            [
                'name'          => 'Pelaksanaan Tindakan Keperawatan',
                'kategori'      => self::KATEGORI_PELAKSANAAN,
                'weight'        => 40,
                'description'   => 'Ketepatan dan keterampilan dalam melaksanakan tindakan',
                'mata_praktik'  => 'Keperawatan Dasar',
                'tingkat_kelas' => self::TINGKAT_X,
                'sop_checklist' => [
                    'Melakukan cuci tangan sebelum tindakan',
                    'Menggunakan APD sesuai prosedur',
                    'Melaksanakan tindakan sesuai SOP',
                    'Mengaplikasikan prinsip steril/aseptik',
                    'Menunjukkan keterampilan yang tepat',
                ],
            ],
            [
                'name'          => 'Hasil dan Evaluasi',
                'kategori'      => self::KATEGORI_HASIL,
                'weight'        => 25,
                'description'   => 'Kualitas hasil tindakan dan kemampuan evaluasi',
                'mata_praktik'  => 'Keperawatan Dasar',
                'tingkat_kelas' => self::TINGKAT_X,
                'sop_checklist' => [
                    'Hasil tindakan sesuai standar',
                    'Melakukan evaluasi hasil',
                    'Mendokumentasikan dengan benar',
                    'Memberikan edukasi kepada pasien/keluarga',
                    'Melakukan tindak lanjut yang tepat',
                ],
            ],
            [
                'name'          => 'Sikap Profesional Keperawatan',
                'kategori'      => self::KATEGORI_SIKAP,
                'weight'        => 15,
                'description'   => 'Sikap dan perilaku profesional selama praktik',
                'mata_praktik'  => 'Keperawatan Dasar',
                'tingkat_kelas' => self::TINGKAT_X,
                'sop_checklist' => [
                    'Berkomunikasi dengan baik',
                    'Menunjukkan empati dan caring',
                    'Menjaga privacy dan confidentiality',
                    'Bekerja sama dalam tim',
                    'Menunjukkan tanggung jawab profesional',
                ],
            ],
        ];
    }

    public static function getDefaultKriteriaFarmasi(): array
    {
        return [
            [
                'name'          => 'Persiapan dan Identifikasi',
                'kategori'      => self::KATEGORI_PERSIAPAN,
                'weight'        => 25,
                'description'   => 'Persiapan workspace dan identifikasi obat/bahan',
                'mata_praktik'  => 'Farmasi Dasar',
                'tingkat_kelas' => self::TINGKAT_XI,
                'sop_checklist' => [
                    'Menyiapkan area kerja yang bersih',
                    'Mengidentifikasi obat/bahan dengan benar',
                    'Memeriksa tanggal kadaluwarsa',
                    'Menyiapkan alat timbang dan ukur',
                    'Menggunakan APD yang sesuai',
                ],
            ],
            [
                'name'          => 'Teknik Pembuatan/Peracikan',
                'kategori'      => self::KATEGORI_PELAKSANAAN,
                'weight'        => 35,
                'description'   => 'Keterampilan dalam pembuatan/peracikan obat',
                'mata_praktik'  => 'Farmasi Dasar',
                'tingkat_kelas' => self::TINGKAT_XI,
                'sop_checklist' => [
                    'Menerapkan teknik aseptik',
                    'Melakukan penimbangan dengan akurat',
                    'Menggunakan teknik pencampuran yang benar',
                    'Mengikuti formula yang ditetapkan',
                    'Menjaga stabilitas sediaan',
                ],
            ],
            [
                'name'          => 'Kontrol Kualitas dan Kemasan',
                'kategori'      => self::KATEGORI_HASIL,
                'weight'        => 25,
                'description'   => 'Pemeriksaan kualitas dan pengemasan hasil',
                'mata_praktik'  => 'Farmasi Dasar',
                'tingkat_kelas' => self::TINGKAT_XI,
                'sop_checklist' => [
                    'Memeriksa organoleptik sediaan',
                    'Melakukan uji kualitas dasar',
                    'Mengemas dengan benar',
                    'Membuat etiket yang sesuai',
                    'Menyimpan sediaan dengan tepat',
                ],
            ],
            [
                'name'          => 'Etika dan Keselamatan Kerja',
                'kategori'      => self::KATEGORI_SIKAP,
                'weight'        => 15,
                'description'   => 'Penerapan etika profesi dan K3',
                'mata_praktik'  => 'Farmasi Dasar',
                'tingkat_kelas' => self::TINGKAT_XI,
                'sop_checklist' => [
                    'Mematuhi prinsip K3 laboratorium',
                    'Menerapkan Good Manufacturing Practice',
                    'Menjaga kerahasiaan resep',
                    'Bekerja dengan teliti dan hati-hati',
                    'Mengelola limbah dengan benar',
                ],
            ],
        ];
    }
}
