<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix data mata_praktik yang salah di assessment_criteria.
 *
 * Temuan dari debug endpoint:
 * Kriteria "Peracikan dan Formulasi Obat" (id=30,31,32,33) dan
 * "Pemeriksaan Golongan Darah" (id=27,28,29) keduanya punya
 * mata_praktik = 'Dasar-Dasar Teknik Laboratorium Medik',
 * padahal seharusnya masing-masing punya mata_praktik sendiri.
 *
 * Perbaikan:
 * - Kriteria yang namanya mengandung "Peracikan dan Formulasi Obat"
 *   → mata_praktik = 'Peracikan dan Formulasi Obat'
 * - Kriteria yang namanya mengandung "Pemeriksaan Golongan Darah" atau
 *   "Analitik" (TLM)
 *   → mata_praktik = 'Pemeriksaan Golongan Darah'
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Fix kriteria Peracikan dan Formulasi Obat ─────────────────────
        // id=30,31,32,33 — namanya mengandung 'Peracikan' atau 'Formulasi'
        // tapi mata_praktik diisi 'Dasar-Dasar Teknik Laboratorium Medik'
        DB::table('assessment_criteria')
            ->where(function ($q) {
                $q->where('name', 'like', '%Peracikan%')
                  ->orWhere('name', 'like', '%Formulasi%');
            })
            ->where('mata_praktik', 'Dasar-Dasar Teknik Laboratorium Medik')
            ->update(['mata_praktik' => 'Peracikan dan Formulasi Obat']);

        // ── Fix kriteria Pemeriksaan Golongan Darah ───────────────────────
        // id=27,28,29 — namanya mengandung 'Golongan Darah' atau 'Analitik'
        // tapi mata_praktik diisi 'Dasar-Dasar Teknik Laboratorium Medik'
        DB::table('assessment_criteria')
            ->where(function ($q) {
                $q->where('name', 'like', '%Golongan Darah%')
                  ->orWhere('name', 'like', '%Analitik%');
            })
            ->where('mata_praktik', 'Dasar-Dasar Teknik Laboratorium Medik')
            ->update(['mata_praktik' => 'Pemeriksaan Golongan Darah']);

        // ── Verifikasi hasil setelah update ──────────────────────────────
        // Log jumlah kriteria per mata_praktik untuk konfirmasi
        $counts = DB::table('assessment_criteria')
            ->select('mata_praktik', DB::raw('COUNT(*) as jml'))
            ->whereNull('deleted_at')
            ->groupBy('mata_praktik')
            ->orderBy('mata_praktik')
            ->get();

        foreach ($counts as $row) {
            \Illuminate\Support\Facades\Log::info(
                "assessment_criteria mata_praktik=[{$row->mata_praktik}] jml={$row->jml}"
            );
        }
    }

    public function down(): void
    {
        // Rollback: kembalikan ke nilai lama (hanya id yang diketahui)
        DB::table('assessment_criteria')
            ->whereIn('id', [27, 28, 29, 30, 31, 32, 33])
            ->update(['mata_praktik' => 'Dasar-Dasar Teknik Laboratorium Medik']);
    }
};
