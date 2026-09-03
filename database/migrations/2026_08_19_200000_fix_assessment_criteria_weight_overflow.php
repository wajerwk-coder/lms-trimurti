<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Perbaiki bobot kriteria yang melebihi 100 atau tidak valid.
 * Jika total bobot per grup mata_praktik melebihi 100,
 * normalisasi ulang agar totalnya = 100.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Konversi weight desimal lama (0.20 dll) ke integer (20) jika belum
        DB::statement("
            UPDATE assessment_criteria
            SET weight = ROUND(weight * 100)
            WHERE weight > 0 AND weight < 2
        ");

        // 2. Ambil semua grup mata_praktik dan periksa totalnya
        $groups = DB::table('assessment_criteria')
            ->select('mata_praktik')
            ->whereNotNull('mata_praktik')
            ->where('mata_praktik', '!=', '')
            ->where('is_active', true)
            ->groupBy('mata_praktik')
            ->get();

        foreach ($groups as $group) {
            $criteria = DB::table('assessment_criteria')
                ->where('mata_praktik', $group->mata_praktik)
                ->where('is_active', true)
                ->get();

            $totalWeight = $criteria->sum('weight');

            // Jika total bobot jauh melebihi 100 (misal 400), normalisasi
            if ($totalWeight > 110 && $totalWeight > 0) {
                foreach ($criteria as $c) {
                    $newWeight = (int) round(($c->weight / $totalWeight) * 100);
                    DB::table('assessment_criteria')
                        ->where('id', $c->id)
                        ->update(['weight' => max(1, $newWeight)]);
                }
            }
        }

        // 3. Kriteria tanpa mata_praktik — jika weight > 100, set ke 10
        DB::table('assessment_criteria')
            ->where(function($q) {
                $q->whereNull('mata_praktik')->orWhere('mata_praktik', '');
            })
            ->where('weight', '>', 100)
            ->update(['weight' => 10]);
    }

    public function down(): void
    {
        // Tidak bisa di-rollback karena data aslinya tidak diketahui
    }
};
