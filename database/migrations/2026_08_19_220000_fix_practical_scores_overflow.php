<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Perbaiki nilai praktik yang melebihi 100 di DB.
 * Nilai overflow terjadi karena bug normalisasi bobot sebelumnya.
 * Summary record (criteria_id = null) yang > 100 → di-cap ke 100.
 * Detail record per kriteria (criteria_id != null) yang > 100 → di-cap ke 100.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cap semua nilai > 100 menjadi 100
        DB::table('practical_scores')
            ->whereNotNull('score')
            ->where('score', '>', 100)
            ->update(['score' => 100]);
    }

    public function down(): void
    {
        // Tidak bisa di-rollback karena nilai aslinya tidak diketahui
    }
};
