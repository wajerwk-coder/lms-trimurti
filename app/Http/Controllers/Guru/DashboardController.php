<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\Practical;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\ExamSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:guru');
    }

    public function index(): View
    {
        $guruId    = Auth::id();
        $today     = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd   = $today->copy()->endOfWeek();

        // ── Stats utama ──────────────────────────────────────────────────
        $pendingGradingCount = AssignmentSubmission::join(
                'assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id'
            )
            ->where('assignments.guru_id', $guruId)
            ->whereNull('assignment_submissions.score')
            ->count();

        $stats = [
            'total_materials'   => Material::where('guru_id', $guruId)->count(),
            'total_assignments' => Assignment::where('guru_id', $guruId)->count(),
            'total_practicals'  => Practical::where('guru_id', $guruId)->count(),
            'total_students'    => DB::table('users_central')->where('role', 'siswa')->count(),
            'pending_grading'   => $pendingGradingCount,
            'today_attendance'  => Attendance::where('recorded_by', $guruId)
                ->whereDate('date', $today)
                ->count(),
            'week_attendance'   => Attendance::where('recorded_by', $guruId)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->count(),
        ];

        // ── Data terbaru ─────────────────────────────────────────────────
        $recentMaterials = Material::where('guru_id', $guruId)
            ->withCount('downloads')
            ->latest()
            ->take(5)
            ->get();

        $recentAssignments = Assignment::withCount([
            'submissions',
            'submissions as ungraded_count' => fn($q) => $q->whereNull('score'),
        ])
        ->where('guru_id', $guruId)
        ->latest()
        ->take(5)
        ->get();

        $recentPracticals = Practical::withCount('scores')
            ->where('guru_id', $guruId)
            ->latest()
            ->take(5)
            ->get();

        // ── Submissions perlu dinilai ─────────────────────────────────────
        $recentSubmissions = AssignmentSubmission::whereHas('assignment', fn($q) => $q->where('guru_id', $guruId))
            ->whereNull('score')
            ->with(['assignment', 'siswa'])
            ->latest()
            ->take(8)
            ->get();

        // ── Grafik absensi mingguan — fix 'present' → 'hadir' ────────────
        $weeklyAttendance = DB::select('
            SELECT DATE(date) as date, COUNT(*) as total,
                   SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as present
            FROM attendances
            WHERE recorded_by = ?
              AND date BETWEEN ? AND ?
            GROUP BY DATE(date)
            ORDER BY DATE(date) ASC
        ', [$guruId, $weekStart->toDateString(), $weekEnd->toDateString()]);

        // ── Top materials by download ─────────────────────────────────────
        $topMaterials = Material::where('guru_id', $guruId)
            ->withCount('downloads')
            ->orderBy('downloads_count', 'desc')
            ->take(5)
            ->get();

        // ── Deadline mendatang (2 minggu ke depan) ───────────────────────
        $upcomingDeadlines = Assignment::where('guru_id', $guruId)
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addWeeks(2))
            ->with('subject')
            ->orderBy('due_date')
            ->take(6)
            ->get();

        // ── Jadwal ujian mendatang ───────────────────────────────────────
        $upcomingExams = collect();
        try {
            $upcomingExams = ExamSchedule::where('is_published', true)
                ->where('start_time', '>', now())
                ->where('start_time', '<=', now()->addWeeks(4))
                ->with(['subject', 'kelas'])
                ->orderBy('start_time')
                ->take(5)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController: upcomingExams query failed — ' . $e->getMessage());
        }

        // ── Notifikasi header bell ───────────────────────────────────────
        $notifications = collect();
        $unreadCount   = 0;
        try {
            $notifications = DB::table('notifications')
                ->where('penerima_id', $guruId)
                ->latest()
                ->limit(10)
                ->get();
            $unreadCount = DB::table('notifications')
                ->where('penerima_id', $guruId)
                ->whereNull('read_at')
                ->count();
        } catch (\Throwable $e) {
            // Notifications table mungkin tidak ada kolom yang diharapkan
        }

        // ── Aktivitas terbaru (placeholder) ──────────────────────────────
        $recentActivities = collect();

        Log::info('Guru dashboard accessed', ['guru_id' => $guruId, 'ip' => request()->ip()]);

        return view('guru.dashboard', compact(
            'stats',
            'recentMaterials',
            'recentAssignments',
            'recentPracticals',
            'recentSubmissions',
            'weeklyAttendance',
            'topMaterials',
            'upcomingDeadlines',
            'upcomingExams',
            'recentActivities',
            'notifications',
            'unreadCount',
            'today',
            'weekStart',
            'weekEnd'
        ));
    }

    /**
     * Get quick stats for dashboard (AJAX).
     */
    public function getQuickStats(): JsonResponse
    {
        $guruId = Auth::id();
        $today  = Carbon::today();

        return response()->json([
            'today_materials'  => Material::where('guru_id', $guruId)->whereDate('created_at', $today)->count(),
            'today_assignments'=> Assignment::where('guru_id', $guruId)->whereDate('created_at', $today)->count(),
            'today_practicals' => Practical::where('guru_id', $guruId)->whereDate('created_at', $today)->count(),
            'attendance_rate'  => Attendance::where('recorded_by', $guruId)
                ->whereDate('date', $today)
                ->selectRaw('ROUND((SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*),0)), 2) as rate')
                ->value('rate') ?? 0,
        ]);
    }
}