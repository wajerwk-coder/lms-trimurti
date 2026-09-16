<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Pastikan kolom score di practical_scores bertipe DECIMAL(8,2)
 * agar tidak ada "Out of range" error saat simpan nilai 0–100.
 *
 * Bug sebelumnya: score yang dihitung salah bisa mencapai 1375+
 * karena checkedSop berisi index dari semua kriteria digabung.
 * Fix di controller sudah membatasi score ke 0–100.
 * Migration ini memastikan kolom DB juga menerima nilai tersebut.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        if (Schema::hasTable('practical_scores')) {
            // Clamp nilai yang sudah terlanjur out-of-range sebelum alter
            DB::statement("UPDATE practical_scores SET score = 100 WHERE score > 100");
            DB::statement("UPDATE practical_scores SET score = 0   WHERE score < 0");

            Schema::table('practical_scores', function (Blueprint $table) {
                // DECIMAL(8,2) → bisa simpan 0.00 – 999999.99 (lebih dari cukup untuk 0–100)
                $table->decimal('score',     8, 2)->nullable()->change();
                $table->decimal('max_score', 8, 2)->default(100.00)->change();
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Tidak perlu rollback — DECIMAL(8,2) lebih aman dari sebelumnya
    }
};
