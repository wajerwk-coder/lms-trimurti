<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Practical;
use App\Models\NilaiPraktik;
use App\Models\AssignmentSubmission;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PeriodReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ── Index: ringkasan semua periode ────────────────────────────────────

    public function index(): View
    {
        $periods = AcademicPeriod::withCount([
            'kelas',
        ])
        ->orderByDesc('academic_year')
        ->orderByRaw("FIELD(semester,'ganjil','genap')")
        ->get()
        ->map(function ($period) {
            $pid = $period->id;

            $period->stat_materials   = Material::where('academic_period_id', $pid)->count();
            $period->stat_assignments = Assignment::where('academic_period_id', $pid)->count();
            $period->stat_practicals  = Practical::where('academic_period_id', $pid)->count();
            $period->stat_attendances = Attendance::where('academic_period_id', $pid)->count();

            // Rata-rata nilai tugas
            $period->avg_assignment = round(
                AssignmentSubmission::whereHas('assignment', fn($q) =>
                    $q->where('academic_period_id', $pid)
                )->avg('score') ?? 0, 1
            );

            // Rata-rata nilai praktikum (summary record: criteria_id = null)
            $period->avg_practical = round(
                NilaiPraktik::whereHas('practical', fn($q) =>
                    $q->where('academic_period_id', $pid)
                )->whereNull('criteria_id')->avg('score') ?? 0, 1
            );

            // Tingkat kehadiran
            $totalAbsensi = $period->stat_attendances;
            $hadirCount   = Attendance::where('academic_period_id', $pid)
                ->where('status', 'hadir')->count();
            $period->attendance_rate = $totalAbsensi > 0
                ? round(($hadirCount / $totalAbsensi) * 100, 1)
                : 0;

            return $period;
        });

        $activePeriod = AcademicPeriod::getActive();

        return view('admin.period-reports.index', compact('periods', 'activePeriod'));
    }

    // ── Show: detail satu periode ─────────────────────────────────────────

    public function show(AcademicPeriod $period): View
    {
        $pid = $period->id;

        // ── Statistik ringkas ─────────────────────────────────────────────
        $stats = [
            'kelas'       => Kelas::where('academic_period_id', $pid)->count(),
            'materials'   => Material::where('academic_period_id', $pid)->count(),
            'assignments' => Assignment::where('academic_period_id', $pid)->count(),
            'practicals'  => Practical::where('academic_period_id', $pid)->count(),
            'attendances' => Attendance::where('academic_period_id', $pid)->count(),
        ];

        // Rata-rata nilai
        $stats['avg_assignment'] = round(
            AssignmentSubmission::whereHas('assignment', fn($q) =>
                $q->where('academic_period_id', $pid)
            )->avg('score') ?? 0, 1
        );
        $stats['avg_practical'] = round(
            NilaiPraktik::whereHas('practical', fn($q) =>
                $q->where('academic_period_id', $pid)
            )->whereNull('criteria_id')->avg('score') ?? 0, 1
        );

        // Tingkat kehadiran
        $hadirCount = Attendance::where('academic_period_id', $pid)
            ->where('status', 'hadir')->count();
        $stats['attendance_rate'] = $stats['attendances'] > 0
            ? round(($hadirCount / $stats['attendances']) * 100, 1)
            : 0;

        // ── Aktivitas per kelas ───────────────────────────────────────────
        $kelasList = Kelas::with('jurusan')
            ->where('academic_period_id', $pid)
            ->withCount('siswa')
            ->orderBy('grade')
            ->orderBy('name')
            ->get()
            ->map(function ($kelas) use ($pid) {
                $kelas->count_materials   = Material::where('academic_period_id', $pid)
                    ->where('kelas_id', $kelas->id)->count();
                $kelas->count_assignments = Assignment::where('academic_period_id', $pid)
                    ->where('kelas_id', $kelas->id)->count();
                $kelas->count_practicals  = Practical::where('academic_period_id', $pid)
                    ->where('kelas_id', $kelas->id)->count();
                $kelas->count_attendances = Attendance::where('academic_period_id', $pid)
                    ->where('kelas_id', $kelas->id)->count();

                // Avg nilai tugas kelas ini
                $kelas->avg_assignment = round(
                    AssignmentSubmission::whereHas('assignment', fn($q) =>
                        $q->where('academic_period_id', $pid)->where('kelas_id', $kelas->id)
                    )->avg('score') ?? 0, 1
                );
                // Avg nilai praktikum kelas ini
                $kelas->avg_practical = round(
                    NilaiPraktik::whereHas('practical', fn($q) =>
                        $q->where('academic_period_id', $pid)->where('kelas_id', $kelas->id)
                    )->whereNull('criteria_id')->avg('score') ?? 0, 1
                );

                return $kelas;
            });

        // ── Ringkasan absensi per status ──────────────────────────────────
        $absensiSummary = Attendance::where('academic_period_id', $pid)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $absensiSummary = [
            'hadir' => $absensiSummary['hadir'] ?? 0,
            'izin'  => $absensiSummary['izin']  ?? 0,
            'sakit' => $absensiSummary['sakit'] ?? 0,
            'alpha' => $absensiSummary['alpha'] ?? 0,
        ];

        // ── Materi terbaru ────────────────────────────────────────────────
        $recentMaterials = Material::with(['guru', 'subject', 'kelas'])
            ->where('academic_period_id', $pid)
            ->latest()
            ->limit(8)
            ->get();

        // ── Tugas terbaru ─────────────────────────────────────────────────
        $recentAssignments = Assignment::with(['guru', 'subject', 'kelas'])
            ->where('academic_period_id', $pid)
            ->latest()
            ->limit(8)
            ->get();

        // ── Praktikum terbaru ─────────────────────────────────────────────
        $recentPracticals = Practical::with(['guru', 'subject', 'kelas'])
            ->where('academic_period_id', $pid)
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.period-reports.show', compact(
            'period', 'stats', 'kelasList',
            'absensiSummary', 'recentMaterials',
            'recentAssignments', 'recentPracticals'
        ));
    }
}
