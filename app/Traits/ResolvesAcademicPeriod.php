<?php

namespace App\Traits;

use App\Models\AcademicPeriod;
use App\Models\Kelas;

/**
 * Trait ResolvesAcademicPeriod
 *
 * Digunakan di controller untuk mengambil academic_period_id
 * secara otomatis dari:
 *   1. Kelas yang dipilih di form (jika kelas sudah dikaitkan ke periode)
 *   2. Periode aktif global (fallback jika kelas tidak punya periode)
 *
 * Cara pakai di controller:
 *   use \App\Traits\ResolvesAcademicPeriod;
 *   ...
 *   $periodId = $this->resolvePeriodId($request->kelas_id ?? $request->class_id);
 */
trait ResolvesAcademicPeriod
{
    /**
     * Ambil academic_period_id dari kelas atau dari periode aktif global.
     *
     * @param  int|string|null $kelasId  ID kelas yang dipilih user (bisa null)
     * @return int|null
     */
    protected function resolvePeriodId(int|string|null $kelasId = null): ?int
    {
        // Prioritas 1: ambil dari kolom academic_period_id kelas yang dipilih
        if ($kelasId) {
            $periodId = Kelas::where('id', $kelasId)
                ->value('academic_period_id');
            if ($periodId) {
                return (int) $periodId;
            }
        }

        // Prioritas 2: gunakan periode aktif global
        return AcademicPeriod::getActive()?->id;
    }

    /**
     * Ambil object AcademicPeriod aktif (cached dalam request lifecycle).
     */
    protected function getActivePeriod(): ?AcademicPeriod
    {
        static $cached = null;
        if ($cached === null) {
            $cached = AcademicPeriod::getActive() ?? false;
        }
        return $cached ?: null;
    }
}
