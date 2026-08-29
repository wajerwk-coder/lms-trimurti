<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Jurusan;
use App\Models\Kelas;

/**
 * Model untuk tabel `siswa`.
 * Kolom: id, user_id, nis, nisn, jenis_kelamin, tempat_lahir, tanggal_lahir,
 *        alamat, no_telepon, kelas_id, major, tahun_ajaran, nama_ortu,
 *        no_telepon_ortu, golongan_darah, riwayat_penyakit, alergi,
 *        info_kesehatan, foto, status, created_at, updated_at, deleted_at
 */
class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_telepon',
        'kelas_id',
        'major',
        'tahun_ajaran',
        'nama_ortu',
        'no_telepon_ortu',
        'golongan_darah',
        'riwayat_penyakit',
        'alergi',
        'info_kesehatan',
        'foto',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserCentral::class, 'user_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Jurusan via Kelas (hasManyThrough tidak bisa dibalik,
     * jadi gunakan hasOneThrough dari Siswa → Kelas → Jurusan).
     */
    public function jurusan(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Jurusan::class,  // target
            Kelas::class,    // through
            'id',            // FK di classes yang matching dengan local key siswa.kelas_id
            'id',            // FK di jurusans yang matching dengan classes.jurusan_id
            'kelas_id',      // local key di siswa → classes.id
            'jurusan_id'     // local key di classes → jurusans.id
        );
    }

    /**
     * Accessor shortcut — ambil nama jurusan tanpa eager load berlebih
     */
    public function getJurusanNameAttribute(): ?string
    {
        return $this->kelas?->jurusan?->name;
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'siswa_id', 'user_id');
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'siswa_id', 'user_id');
    }

    public function practicalScores()
    {
        return $this->hasMany(NilaiPraktik::class, 'siswa_id', 'user_id');
    }

    // ── Accessors ────────────────────────────────────────────────────────

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->user?->name ?? 'S') . '&background=EBF4FF&color=7F9CF5';
    }

    public function getNamaLengkapAttribute(): string
    {
        return $this->user?->name ?? '—';
    }
}
