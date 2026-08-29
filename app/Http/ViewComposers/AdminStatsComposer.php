<?php

namespace App\Http\ViewComposers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\UserCentral;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\KriteriaPenilaian;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\Practical;
use App\Models\ExamSchedule;

class AdminStatsComposer
{
    public function compose(View $view): void
    {
        // Optional: fast-disable via env
        if (env('ADMIN_STATS_DISABLE', false)) {
            $view->with('stats', []);
            return;
        }

        // Fast-fail if DB connection is not available
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $view->with('stats', []);
            return;
        }

        $ttl = (int) env('ADMIN_STATS_CACHE_SECONDS', 300);

        $stats = Cache::remember('admin_stats_counts', $ttl, function () {
            return [
                'total_users'      => $this->safeCount(UserCentral::class),
                'total_siswa'      => $this->safeCount(Siswa::class),
                'total_guru'       => $this->safeCount(Guru::class),
                'total_classes'    => $this->safeCount(Kelas::class),
                'total_majors'     => $this->safeCount(Jurusan::class),
                'total_criteria'   => $this->safeCount(KriteriaPenilaian::class),
                'total_materials'  => $this->safeCount(Material::class),
                'total_assignments'=> $this->safeCount(Assignment::class),
                'total_practicals' => $this->safeCount(Practical::class),
                'total_exams'      => $this->safeCount(ExamSchedule::class),
            ];
        });

        $view->with('stats', $stats);
    }

    private function safeCount(string $modelClass): int
    {
        try {
            if (!class_exists($modelClass)) {
                return 0;
            }
            return (int) $modelClass::query()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
