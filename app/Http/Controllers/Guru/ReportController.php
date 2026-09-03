<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\Practical;
use App\Models\Attendance;
use App\Models\NilaiPraktik;
use App\Models\AssignmentSubmission;
use App\Models\MaterialDownload;
use App\Models\Subject;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'guru']);
    }

    /**
     * Display the reports dashboard.
     */
    public function index(): View
    {
        $guruId    = Auth::id();
        $startDate = now()->subMonth()->format('Y-m-d');
        $endDate   = now()->format('Y-m-d');

        // ── Stats sederhana — 4 query ringkas ────────────────────────────────
        $stats = [
            'total_materials'    => Material::where('guru_id', $guruId)->count(),
            'total_assignments'  => Assignment::where('guru_id', $guruId)->count(),
            'total_practicals'   => Practical::where('guru_id', $guruId)->count(),
            'total_attendance'   => Attendance::where('recorded_by', $guruId)
                                        ->whereBetween('date', [$startDate, $endDate])
                                        ->count(),
            'graded_assignments' => AssignmentSubmission::whereHas('assignment',
                                        fn($q) => $q->where('guru_id', $guruId))
                                        ->whereNotNull('score')->count(),
            'pending_assignments'=> AssignmentSubmission::whereHas('assignment',
                                        fn($q) => $q->where('guru_id', $guruId))
                                        ->whereNull('score')->count(),
            'materials_downloads'=> MaterialDownload::whereHas('material',
                                        fn($q) => $q->where('guru_id', $guruId))
                                        ->whereBetween('downloaded_at', [$startDate, $endDate])
                                        ->count(),
            'average_practical_score' => round(NilaiPraktik::whereNull('criteria_id')
                                        ->whereHas('practical', fn($q) => $q->where('guru_id', $guruId))
                                        ->avg('score') ?? 0, 1),
        ];

        // ── Chart data — pakai single GROUP BY query per tabel ────────────────
        $monthlyData = $this->getMonthlyReportData($guruId);

        $viewName = request()->route()->getName() === 'guru.reports.index'
            ? 'guru.reports.index'
            : 'guru.laporan.index';

        return view($viewName, compact('stats', 'startDate', 'endDate', 'monthlyData'));
    }

    /**
     * Monthly chart data — single query per tabel, bukan loop per bulan.
     */
    private function getMonthlyReportData(int $guruId): array
    {
        $since = Carbon::now()->subMonths(5)->startOfMonth();

        // Bangun 6 label bulan
        $labels   = [];
        $labelMap = []; // 'YYYY-MM' => index
        $cur      = $since->copy();
        for ($i = 0; $i < 6; $i++) {
            $key            = $cur->format('Y-m');
            $labels[]       = $cur->translatedFormat('M Y');
            $labelMap[$key] = $i;
            $cur->addMonth();
        }

        $zeros = array_fill(0, 6, 0);

        // Materials — 1 query
        $matRows = DB::table('materials')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->where('guru_id', $guruId)
            ->where('created_at', '>=', $since)
            ->whereNull('deleted_at')
            ->groupBy('ym')->get();

        // Assignments — 1 query
        $asgRows = DB::table('assignments')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->where('guru_id', $guruId)
            ->where('created_at', '>=', $since)
            ->whereNull('deleted_at')
            ->groupBy('ym')->get();

        // Practicals — 1 query
        $pracRows = DB::table('practicals')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->where('guru_id', $guruId)
            ->where('created_at', '>=', $since)
            ->whereNull('deleted_at')
            ->groupBy('ym')->get();

        // Attendance — 1 query
        $attRows = DB::table('attendances')
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, COUNT(*) as cnt")
            ->where('recorded_by', $guruId)
            ->where('date', '>=', $since->format('Y-m-d'))
            ->whereNull('deleted_at')
            ->groupBy('ym')->get();

        // Map ke array index
        $materials   = $zeros;
        $assignments = $zeros;
        $practicals  = $zeros;
        $attendance  = $zeros;

        foreach ($matRows  as $r) { if (isset($labelMap[$r->ym])) $materials[$labelMap[$r->ym]]   = $r->cnt; }
        foreach ($asgRows  as $r) { if (isset($labelMap[$r->ym])) $assignments[$labelMap[$r->ym]] = $r->cnt; }
        foreach ($pracRows as $r) { if (isset($labelMap[$r->ym])) $practicals[$labelMap[$r->ym]]  = $r->cnt; }
        foreach ($attRows  as $r) { if (isset($labelMap[$r->ym])) $attendance[$labelMap[$r->ym]]  = $r->cnt; }

        return compact('labels', 'materials', 'assignments', 'practicals', 'attendance');
    }

    /**
     * Show practical reports.
     */
    public function praktik(Request $request): View
    {
        $guruId = Auth::id();

        $filters = [
            'start_date' => $request->start_date ?? Carbon::now()->subMonth()->format('Y-m-d'),
            'end_date'   => $request->end_date   ?? Carbon::now()->format('Y-m-d'),
            'kelas'      => $request->kelas,
        ];

        $query = Practical::withCount('scores')
            ->where('guru_id', $guruId)
            ->whereBetween('due_date', [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']);

        if ($filters['kelas']) {
            $query->where('kelas_id', $filters['kelas']);
        }

        $practicals = $query->latest()->paginate(15);

        $scoreBase = NilaiPraktik::whereHas('practical', function ($q) use ($guruId, $filters) {
            $q->where('guru_id', $guruId)
              ->whereBetween('due_date', [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']);
            if ($filters['kelas']) $q->where('kelas_id', $filters['kelas']);
        });

        $practicalStats = [
            'total_siswa'   => (clone $scoreBase)->whereNull('criteria_id')->distinct('siswa_id')->count('siswa_id'),
            'average_score' => round((clone $scoreBase)->whereNull('criteria_id')->avg('score') ?? 0, 1),
            'total_graded'  => (clone $scoreBase)->whereNull('criteria_id')->count(),
        ];

        $classes = \App\Models\Kelas::orderBy('name')->pluck('name', 'id');

        return view('guru.laporan.praktik', compact('practicals', 'practicalStats', 'classes', 'filters'));
    }

    public function absensi(Request $request): View
    {
        $guruId = Auth::id();

        $filters = [
            'start_date' => $request->start_date ?? Carbon::now()->subMonth()->format('Y-m-d'),
            'end_date'   => $request->end_date   ?? Carbon::now()->format('Y-m-d'),
            'kelas'      => $request->kelas,
            'status'     => $request->status,
        ];

        // Filter kelas: ambil users_central.id dari tabel siswa
        $siswaUcIds = $filters['kelas']
            ? Siswa::where('kelas_id', $filters['kelas'])->pluck('user_id')
            : null;

        $query = Attendance::with(['siswa', 'subject', 'kelas'])
            ->where('recorded_by', $guruId)
            ->whereBetween('date', [$filters['start_date'], $filters['end_date']])
            ->when($siswaUcIds, fn($q) => $q->whereIn('siswa_id', $siswaUcIds))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']));

        $attendance = $query->orderBy('date', 'desc')->paginate(20);

        $statsQuery = Attendance::selectRaw('status, COUNT(*) as count')
            ->where('recorded_by', $guruId)
            ->whereBetween('date', [$filters['start_date'], $filters['end_date']])
            ->when($siswaUcIds, fn($q) => $q->whereIn('siswa_id', $siswaUcIds))
            ->groupBy('status')
            ->get();

        $summaryStats = [
            'total_days'      => Carbon::parse($filters['start_date'])->diffInDays(Carbon::parse($filters['end_date'])) + 1,
            'total_records'   => $attendance->total(),
            'present_count'   => $statsQuery->where('status', 'hadir')->first()?->count ?? 0,
            'izin_count'      => $statsQuery->where('status', 'izin')->first()?->count ?? 0,
            'sakit_count'     => $statsQuery->where('status', 'sakit')->first()?->count ?? 0,
            'absent_count'    => $statsQuery->where('status', 'alpha')->first()?->count ?? 0,
            'attendance_rate' => $attendance->total() > 0
                ? round(($statsQuery->where('status', 'hadir')->first()?->count ?? 0) / $attendance->total() * 100, 1)
                : 0,
        ];

        $classes = \App\Models\Kelas::orderBy('name')->pluck('name', 'id');

        $stats = [
            'present_count'   => $summaryStats['present_count'],
            'absent_count'    => $summaryStats['absent_count'],
            'izin_count'      => $summaryStats['izin_count'] + $summaryStats['sakit_count'],
            'attendance_rate' => $summaryStats['attendance_rate'],
        ];

        $attendances  = $attendance; // alias untuk view baru
        $attendanceStats = $statsQuery;

        $viewName = request()->route()->getName() === 'guru.reports.attendance'
            ? 'guru.reports.attendance'
            : 'guru.laporan.absensi';

        return view($viewName, compact(
            'attendance', 'attendances', 'attendanceStats', 'summaryStats', 'classes', 'stats', 'filters'
        ));
    }

    /**
     * Show assignment reports.
     */
    public function tugas(Request $request): View
    {
        $guruId = Auth::id();

        $filters = [
            'start_date' => $request->start_date ?? Carbon::now()->subMonth()->format('Y-m-d'),
            'end_date'   => $request->end_date   ?? Carbon::now()->format('Y-m-d'),
            'status'     => $request->status,
        ];

        $query = Assignment::withCount([
            'submissions',
            'submissions as graded_count'  => fn($q) => $q->whereNotNull('score'),
            'submissions as pending_count' => fn($q) => $q->whereNull('score'),
        ])
        ->where('guru_id', $guruId)
        ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);

        $assignments = $query->latest()->paginate(15);

        $subBase = AssignmentSubmission::whereHas('assignment', fn($q) => $q
            ->where('guru_id', $guruId)
            ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
        );

        $assignmentStats = [
            'total_submissions' => (clone $subBase)->count(),
            'graded_submissions'=> (clone $subBase)->whereNotNull('score')->count(),
            'average_score'     => round((clone $subBase)->whereNotNull('score')->avg('score') ?? 0, 1),
        ];

        return view('guru.laporan.tugas', compact('assignments', 'assignmentStats', 'filters'));
    }

    /**
     * Show material reports.
     */
    public function materi(Request $request): View
    {
        $guruId = Auth::id();

        $filters = [
            'start_date' => $request->start_date ?? Carbon::now()->subMonth()->format('Y-m-d'),
            'end_date'   => $request->end_date   ?? Carbon::now()->format('Y-m-d'),
        ];

        $materials = Material::withCount('downloads')
            ->where('guru_id', $guruId)
            ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
            ->latest()->paginate(15);

        $dlBase = MaterialDownload::whereHas('material', fn($q) => $q
            ->where('guru_id', $guruId)
            ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
        )->whereBetween('downloaded_at', [$filters['start_date'], $filters['end_date']]);

        $materialStats = [
            'total_downloads'  => (clone $dlBase)->count(),
            'total_views'      => Material::where('guru_id', $guruId)
                ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
                ->sum('views_count') ?? 0,
            'most_downloaded'  => Material::where('guru_id', $guruId)
                ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
                ->orderByDesc('downloads_count')->first(),
        ];

        return view('guru.laporan.materi', compact('materials', 'materialStats', 'filters'));
    }

    /**
     * Show attendance reports (English method for guru.reports.attendance route).
     */
    public function attendance(Request $request): View
    {
        return $this->absensi($request);
    }

    /**
     * Show practical reports (English method for guru.reports.practical route).
     */
    public function practical(Request $request): View
    {
        return $this->praktik($request);
    }

    /**
     * Laporan nilai siswa — rekap nilai tugas dan praktikum.
     */
    public function nilai(Request $request): View
    {
        $guruId = Auth::id();

        $filters = [
            'start_date' => $request->start_date ?? Carbon::now()->subMonth()->format('Y-m-d'),
            'end_date'   => $request->end_date   ?? Carbon::now()->format('Y-m-d'),
            'kelas_id'   => $request->kelas_id,
        ];

        // Rekap nilai tugas per siswa
        $nilaiTugasQuery = AssignmentSubmission::with(['siswa', 'assignment.subject'])
            ->whereHas('assignment', fn($q) => $q->where('guru_id', $guruId))
            ->whereNotNull('score')
            ->where(function($q) use ($filters) {
                $q->whereBetween('submitted_at', [
                        $filters['start_date'] . ' 00:00:00',
                        $filters['end_date']   . ' 23:59:59',
                    ])
                  ->orWhere(function($q2) use ($filters) {
                      $q2->whereNull('submitted_at')
                         ->whereBetween('created_at', [
                             $filters['start_date'] . ' 00:00:00',
                             $filters['end_date']   . ' 23:59:59',
                         ]);
                  });
            });

        // Filter kelas: siswa_id ada di tabel siswa yang kelas_id sesuai
        if ($filters['kelas_id']) {
            $siswaUcIds = Siswa::where('kelas_id', $filters['kelas_id'])
                ->pluck('user_id');
            $nilaiTugasQuery->whereIn('siswa_id', $siswaUcIds);
        }

        $nilaiTugas = $nilaiTugasQuery->latest()->get();

        // Rekap nilai praktikum per siswa
        // Tidak filter criteria_id=null karena penilaian praktik bisa pakai criteria_id
        // Group per siswa+praktikum: ambil nilai terbaru atau rata-rata
        $nilaiPraktikQuery = NilaiPraktik::with(['siswa', 'practical.subject'])
            ->whereHas('practical', fn($q) => $q->where('guru_id', $guruId))
            ->whereNotNull('score');

        // Filter tanggal: pakai graded_at jika ada, fallback ke created_at
        $nilaiPraktikQuery->where(function($q) use ($filters) {
            $q->whereBetween('graded_at', [
                    $filters['start_date'] . ' 00:00:00',
                    $filters['end_date']   . ' 23:59:59',
                ])
              ->orWhere(function($q2) use ($filters) {
                  $q2->whereNull('graded_at')
                     ->whereBetween('created_at', [
                         $filters['start_date'] . ' 00:00:00',
                         $filters['end_date']   . ' 23:59:59',
                     ]);
              });
        });

        if ($filters['kelas_id']) {
            $siswaUcIds = $siswaUcIds ?? Siswa::where('kelas_id', $filters['kelas_id'])->pluck('user_id');
            $nilaiPraktikQuery->whereIn('siswa_id', $siswaUcIds);
        }

        // Ambil 1 nilai per siswa per praktikum (criteria_id=null dulu, kalau tidak ada ambil semua)
        $allNilaiPraktik = $nilaiPraktikQuery->latest('graded_at')->get();

        // Deduplikasi: jika ada criteria_id=null pakai itu, jika tidak pakai rata-rata per siswa+praktikum
        $nilaiPraktik = $allNilaiPraktik
            ->groupBy(fn($n) => $n->siswa_id . '_' . $n->practical_id)
            ->map(function($group) {
                // Prioritas: ambil yang criteria_id = null (nilai total)
                $total = $group->whereNull('criteria_id')->first();
                if ($total) return $total;
                // Jika tidak ada, buat aggregate dari criteria
                $avg = $group->avg('score');
                $first = $group->first();
                $first->score = round($avg, 1);
                return $first;
            })
            ->values();

        $kelas = \App\Models\Kelas::orderBy('name')->get();

        $stats = [
            'avg_tugas'   => round($nilaiTugas->avg('score') ?? 0, 1),
            'avg_praktik' => round($nilaiPraktik->avg('score') ?? 0, 1),
            'total_siswa_dinilai' => $nilaiTugas->pluck('siswa_id')->merge($nilaiPraktik->pluck('siswa_id'))->unique()->count(),
        ];

        return view('guru.laporan.nilai', compact('nilaiTugas', 'nilaiPraktik', 'kelas', 'filters', 'stats'));
    }

    /**
     * Laporan siswa — rekap kehadiran, nilai, dan aktivitas per siswa.
     * Dioptimalkan: bulk query per kelas, bukan N+1 per siswa.
     */
    public function siswa(Request $request): View
    {
        $guruId = Auth::id();

        $filters = [
            'start_date' => $request->start_date ?? Carbon::now()->subMonth()->format('Y-m-d'),
            'end_date'   => $request->end_date   ?? Carbon::now()->format('Y-m-d'),
            'kelas_id'   => $request->kelas_id,
        ];

        $kelas = \App\Models\Kelas::orderBy('name')->get();

        // Ambil daftar siswa
        $siswaList = Siswa::with(['user', 'kelas'])
            ->whereNull('deleted_at')
            ->when($filters['kelas_id'], fn($q) => $q->where('kelas_id', $filters['kelas_id']))
            ->get();

        if ($siswaList->isEmpty()) {
            return view('guru.laporan.siswa', compact('filters', 'kelas') + ['siswaData' => collect()]);
        }

        // Ambil semua user_id (users_central.id) dari list siswa
        $ucIds = $siswaList->pluck('user_id')->filter()->values();

        // Bulk query: semua absensi untuk siswa-siswa ini
        $allAbsensi = \App\Models\Attendance::selectRaw('siswa_id, status, COUNT(*) as cnt')
            ->whereIn('siswa_id', $ucIds)
            ->whereBetween('date', [$filters['start_date'], $filters['end_date']])
            ->groupBy('siswa_id', 'status')
            ->get()
            ->groupBy('siswa_id');

        // Bulk query: rata-rata nilai tugas per siswa
        $avgTugasList = AssignmentSubmission::selectRaw('siswa_id, AVG(score) as avg_score')
            ->whereIn('siswa_id', $ucIds)
            ->whereHas('assignment', fn($q) => $q->where('guru_id', $guruId))
            ->whereNotNull('score')
            ->groupBy('siswa_id')
            ->pluck('avg_score', 'siswa_id');

        // Bulk query: rata-rata nilai praktik per siswa
        $avgPraktikList = NilaiPraktik::selectRaw('siswa_id, AVG(score) as avg_score')
            ->whereIn('siswa_id', $ucIds)
            ->whereHas('practical', fn($q) => $q->where('guru_id', $guruId))
            ->whereNull('criteria_id')
            ->whereNotNull('score')
            ->groupBy('siswa_id')
            ->pluck('avg_score', 'siswa_id');

        // Map data per siswa menggunakan hasil bulk query
        $siswaData = $siswaList->map(function ($s) use ($allAbsensi, $avgTugasList, $avgPraktikList) {
            $ucId        = $s->user_id;
            $absensiRows = $allAbsensi->get($ucId, collect());

            $totalAbsensi = $absensiRows->sum('cnt');
            $hadir        = $absensiRows->where('status', 'hadir')->sum('cnt');

            $s->total_absensi = $totalAbsensi;
            $s->hadir         = $hadir;
            $s->pct_hadir     = $totalAbsensi > 0 ? round($hadir / $totalAbsensi * 100) : 0;
            $s->avg_tugas     = isset($avgTugasList[$ucId])   ? round((float)$avgTugasList[$ucId],  1) : null;
            $s->avg_praktik   = isset($avgPraktikList[$ucId]) ? round((float)$avgPraktikList[$ucId], 1) : null;
            return $s;
        });

        return view('guru.laporan.siswa', compact('siswaData', 'kelas', 'filters'));
    }

    /**
     * Generate report (for guru.reports.generate route).
     */
    public function generate(Request $request)
    {
        $type = $request->input('type');
        return $this->export($type, $request);
    }

    /**
     * Export report to PDF.
     */
    public function export($type, Request $request)
    {
        $guruId = Auth::id();

        $validClasses = \App\Models\Kelas::orderBy('name')
            ->pluck('id')
            ->toArray();

        $rules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf',
        ];

        if (!empty($validClasses)) {
            $rules['kelas'] = 'nullable|string|in:' . implode(',', $validClasses);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'kelas' => $request->kelas,
        ];

        $filename = 'laporan-' . $type . '-' . $filters['start_date'] . '-to-' . $filters['end_date'];

        Log::info('Report exported to PDF', [
            'type' => $type,
            'guru_id' => $guruId,
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'kelas' => $filters['kelas'],
            'ip' => $request->ip()
        ]);

        switch ($type) {
            case 'absensi':
                return $this->exportAttendancePdf($filters, $filename, $guruId);

            case 'praktik':
                return $this->exportPracticalPdf($filters, $filename, $guruId);

            case 'tugas':
                return $this->exportAssignmentPdf($filters, $filename, $guruId);

            case 'materi':
                return $this->exportMaterialPdf($filters, $filename, $guruId);

            default:
                return redirect()->back()->with('error', 'Jenis laporan tidak valid');
        }
    }

    private function exportAttendancePdf($filters, $filename, $guruId)
    {
        // Filter kelas: ambil siswa_id (users_central.id) dari tabel siswa
        $siswaUcIds = $filters['kelas']
            ? Siswa::where('kelas_id', $filters['kelas'])->pluck('user_id')
            : null;

        $attendance = Attendance::with(['siswa', 'subject', 'kelas'])
            ->where('recorded_by', $guruId)
            ->whereBetween('date', [$filters['start_date'], $filters['end_date']])
            ->when($siswaUcIds, fn($q) => $q->whereIn('siswa_id', $siswaUcIds))
            ->orderBy('date', 'desc')
            ->limit(1000)
            ->get();

        $stats = Attendance::selectRaw('status, COUNT(*) as count')
            ->where('recorded_by', $guruId)
            ->whereBetween('date', [$filters['start_date'], $filters['end_date']])
            ->when($siswaUcIds, fn($q) => $q->whereIn('siswa_id', $siswaUcIds))
            ->groupBy('status')
            ->get();

        $pdf = Pdf::loadView('guru.laporan.pdf.absensi', compact('attendance', 'stats', 'filters'));
        return $pdf->download($filename . '.pdf');
    }

    private function exportPracticalPdf($filters, $filename, $guruId)
    {
        // Pakai NilaiPraktik (bukan PracticalScore) — model yang benar
        $practicals = Practical::with(['subject', 'kelas'])
            ->withCount([
                'scores as scores_count' => fn($q) => $q->whereNull('criteria_id'),
            ])
            ->where('guru_id', $guruId)
            ->whereBetween('due_date', [
                $filters['start_date'] . ' 00:00:00',
                $filters['end_date']   . ' 23:59:59',
            ])
            ->when($filters['kelas'], fn($q) => $q->where('kelas_id', $filters['kelas']))
            ->latest()->limit(1000)->get();

        // Attach scores per practical untuk view PDF
        $practicals->each(function ($p) {
            $p->setRelation('scores', NilaiPraktik::with('siswa')
                ->where('practical_id', $p->id)
                ->whereNull('criteria_id')
                ->whereNotNull('score')
                ->get());
        });

        $scoreBase = NilaiPraktik::whereHas('practical', function ($q) use ($guruId, $filters) {
            $q->where('guru_id', $guruId)
              ->whereBetween('due_date', [
                  $filters['start_date'] . ' 00:00:00',
                  $filters['end_date']   . ' 23:59:59',
              ]);
            if ($filters['kelas']) $q->where('kelas_id', $filters['kelas']);
        })->whereNull('criteria_id');

        $stats = [
            'total_practicals' => $practicals->count(),
            'total_scores'     => (clone $scoreBase)->count(),
            'average_score'    => round((clone $scoreBase)->avg('score') ?? 0, 1),
        ];

        $pdf = Pdf::loadView('guru.laporan.pdf.praktik', compact('practicals', 'stats', 'filters'));
        return $pdf->download($filename . '.pdf');
    }

    private function exportAssignmentPdf($filters, $filename, $guruId)
    {
        $assignments = Assignment::withCount([
                'submissions',
                'submissions as graded_count' => function($query) {
                    $query->whereNotNull('score');
                }
            ])
            ->where('guru_id', $guruId)
            ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
            ->latest()
            ->limit(1000)
            ->get();

        $stats = [
            'total_assignments' => $assignments->count(),
            'total_submissions' => AssignmentSubmission::whereHas('assignment', function($query) use ($guruId, $filters) {
                $query->where('guru_id', $guruId)
                    ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            })->count(),
            'average_score' => AssignmentSubmission::whereHas('assignment', function($query) use ($guruId, $filters) {
                $query->where('guru_id', $guruId)
                    ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            })->whereNotNull('score')->avg('score') ?? 0,
        ];

        $pdf = Pdf::loadView('guru.laporan.pdf.tugas', compact('assignments', 'stats', 'filters'));
        return $pdf->download($filename . '.pdf');
    }

    private function exportMaterialPdf($filters, $filename, $guruId)
    {
        $materials = Material::withCount('downloads')
            ->where('guru_id', $guruId)
            ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']])
            ->latest()
            ->limit(1000)
            ->get();

        $stats = [
            'total_materials' => $materials->count(),
            'total_downloads' => MaterialDownload::whereHas('material', function($query) use ($guruId, $filters) {
                $query->where('guru_id', $guruId)
                    ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            })->whereBetween('downloaded_at', [$filters['start_date'], $filters['end_date']])->count(),
            'most_downloaded' => $materials->sortByDesc('downloads_count')->first(),
        ];

        $pdf = Pdf::loadView('guru.laporan.pdf.materi', compact('materials', 'stats', 'filters'));
        return $pdf->download($filename . '.pdf');
    }
}