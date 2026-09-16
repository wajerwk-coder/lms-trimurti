<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Retry fix: alter score DECIMAL(8,2) di practical_scores.
 * Migration sebelumnya (000001) gagal karena mencoba alter max_score
 * yang tidak ada di DB Railway.
 *
 * Migration ini menggunakan hasColumn() untuk semua kolom
 * sehingga aman dijalankan di DB manapun.
 *
 * Juga menghapus record migration 000001 yang tercatat FAIL
 * agar tidak menghalangi future migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ── 1. Hapus record migration yang gagal agar tidak block future runs ──
        DB::table('migrations')
            ->where('migration', '2026_09_16_000001_fix_practical_scores_score_column')
            ->delete();

        // ── 2. Fix tabel practical_scores ──────────────────────────────────────
        if (Schema::hasTable('practical_scores')) {
            // Clamp data lama yang out-of-range sebelum alter tipe kolom
            if (Schema::hasColumn('practical_scores', 'score')) {
                DB::statement("UPDATE practical_scores SET score = 100 WHERE score > 100");
                DB::statement("UPDATE practical_scores SET score = 0   WHERE score < 0");

                // Pastikan kolom score bertipe DECIMAL(8,2) — max 999999.99
                // Error sebelumnya: kemungkinan kolom bertipe DECIMAL(5,2) max 999.99
                // atau SMALLINT, yang tidak cukup untuk nilai sementara yang terhitung salah
                DB::statement("ALTER TABLE `practical_scores` MODIFY `score` DECIMAL(8,2) NULL DEFAULT NULL");
            }

            // max_score hanya jika kolom ada (tidak ada di semua environment)
            if (Schema::hasColumn('practical_scores', 'max_score')) {
                DB::statement("ALTER TABLE `practical_scores` MODIFY `max_score` DECIMAL(8,2) NOT NULL DEFAULT '100.00'");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Tidak di-rollback — lebih aman menjaga DECIMAL(8,2)
    }
};
