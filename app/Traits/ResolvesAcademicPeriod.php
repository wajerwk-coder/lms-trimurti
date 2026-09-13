<?php

namespace App\Traits;

use App\Models\AcademicPeriod;
use App\Models\Kelas;

/**
 * Trait ResolvesAcademicPeriod
 *
 * Digunakan di semua controller yang menyimpan aktivitas guru/siswa
 * agar setiap record otomatis terkait ke periode pembelajaran aktif.
 *
 * Strategi resolve (prioritas):
 *   1. Dari kelas yang dipilih → kelas.academic_period_id (paling akurat)
 *   2. Fallback: AcademicPeriod::getActive() (periode aktif global)
 *   3. Fallback terakhir: null (tidak ada periode)
 */
trait ResolvesAcademicPeriod
{
    /**
     * Resolve academic_period_id dari kelas_id atau periode aktif.
     *
     * @param int|null $kelasId  FK ke tabel classes
     * @return int|null
     */
    protected function resolveAcademicPeriodId(?int $kelasId = null): ?int
    {
        // Prioritas 1: dari kelas yang dipilih
        if ($kelasId) {
            $period = Kelas::find($kelasId)?->academic_period_id;
            if ($period) {
                return $period;
            }
        }

        // Prioritas 2: periode aktif global
        return AcademicPeriod::getActive()?->id;
    }

    /**
     * Alias pendek yang dipakai banyak controller: resolvePeriodId()
     */
    protected function resolvePeriodId(?int $kelasId = null): ?int
    {
        return $this->resolveAcademicPeriodId($kelasId);
    }

    /**
     * Shortcut — resolve dari request field kelas_id atau class_id.
     *
     * @param \Illuminate\Http\Request $request
     * @param string ...$fields  nama field input kelas_id dalam request
     * @return int|null
     */
    protected function resolveAcademicPeriodFromRequest(
        \Illuminate\Http\Request $request,
        string ...$fields
    ): ?int {
        // Cari field kelas_id yang terisi
        $kelasId = null;
        foreach ($fields as $field) {
            $val = $request->input($field);
            if ($val) {
                $kelasId = (int) $val;
                break;
            }
        }

        return $this->resolveAcademicPeriodId($kelasId);
    }
}
