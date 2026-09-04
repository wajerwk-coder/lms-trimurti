<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\Practical;
use App\Models\Attendance;
use App\Models\NilaiPraktik;
use App\Models\AssignmentSubmission;
use App\Models\MaterialDownload;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    /**
     * Display the student dashboard.
     */
    public function index(): View
    {
        $siswa = Auth::user();
        $userCentralId = $siswa->id; // ID di tabel users_central
        
        // Load profil siswa (tabel siswa) → kelas_id ada di sini
        $siswaProfile = \App\Models\Siswa::where('user_id', $siswa->id)
            ->with('kelas')
            ->first();
        $kelasId = $siswaProfile?->kelas_id ?? null;

        // Untuk query attendance, submissions, dll → gunakan user_id (users_central.id)
        $siswaId = $userCentralId;

        // Stats for dashboard
        $stats = [
            'total_materials' => Material::whereNotNull('published_at')
                ->where(function ($q) use ($kelasId) {
                    $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
                })
                ->count(),
            // completed_assignments = semua tugas yang sudah DIKUMPULKAN (ada submission)
            // bukan hanya yang sudah dinilai
            'completed_assignments' => AssignmentSubmission::where('siswa_id', $siswaId)
                ->whereNotNull('submitted_at')
                ->count(),
            'completed_practicals' => \App\Models\NilaiPraktik::where('siswa_id', $siswaId)
                ->whereNull('criteria_id')
                ->whereNotNull('score')
                ->count(),
            'attendance_percentage' => $this->calculateAttendanceRate($siswaId),
            'average_score'         => $this->getAverageScore($siswaId),
            'attendance_count'      => Attendance::where('siswa_id', $siswaId)
                ->where('status', 'hadir')
                ->count(),
            'pending_assignments'   => $this->getPendingAssignmentsCount($siswaId, $kelasId),
            'rank' => $this->getStudentRank($siswaId, $kelasId),
        ];

        // Recent materials — dengan subject dan guru
        $recentMaterials = Material::with(['subject', 'guru'])
            ->whereNotNull('published_at')
            ->where(function($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId)
                      ->orWhereNull('kelas_id');
            })
            ->latest()
            ->take(5)
            ->get();

        // Upcoming deadlines
        $upcomingDeadlines = $this->getUpcomingDeadlines($siswaId, $kelasId);

        // Variables for backward compatibility
        $newMaterialsCount = $stats['total_materials'];
        $pendingAssignmentsCount = $this->getPendingAssignmentsCount($siswaId, $kelasId);
        $upcomingPracticalsCount = Practical::whereNotNull('published_at')
            ->where(function($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId)
                      ->orWhereNull('kelas_id');
            })
            ->count();
        $attendancePercentage = $stats['attendance_percentage'];

        return view('siswa.dashboard', compact(
            'stats',
            'recentMaterials',
            'upcomingDeadlines',
            'newMaterialsCount',
            'pendingAssignmentsCount',
            'upcomingPracticalsCount',
            'attendancePercentage',
            'siswaProfile'
        ));
    }

    protected function getUpcomingDeadlines($siswaId, $kelasId)
    {
        $deadlines = [];

        // Tugas yang belum dikumpulkan
        $assignments = Assignment::with('subject')
            ->where('is_published', true)
            ->where(function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
            })
            ->where('due_date', '>', now())
            ->whereDoesntHave('submissions', fn($q) => $q->where('siswa_id', $siswaId))
            ->orderBy('due_date')
            ->take(5)
            ->get();

        foreach ($assignments as $a) {
            $deadlines[] = (object)[
                'id'          => $a->id,
                'title'       => $a->title,
                'type'        => 'assignment',
                'due_date'    => $a->due_date,    // <— pakai due_date
                'subject'     => $a->subject,      // <— relasi subject
                'days_left'   => now()->diffInDays($a->due_date, false),
            ];
        }

        // Praktikum yang belum dinilai
        $practicals = Practical::with('subject')
            ->whereNotNull('published_at')
            ->where(function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
            })
            ->where('due_date', '>', now())
            ->whereDoesntHave('scores', fn($q) => $q->where('siswa_id', $siswaId)->whereNull('criteria_id'))
            ->orderBy('due_date')
            ->take(5)
            ->get();

        foreach ($practicals as $p) {
            $deadlines[] = (object)[
                'id'       => $p->id,
                'title'    => $p->title,
                'type'     => 'practical',
                'due_date' => $p->due_date,
                'subject'  => $p->subject,
                'days_left'=> now()->diffInDays($p->due_date, false),
            ];
        }

        // Urutkan berdasarkan due_date
        usort($deadlines, fn($a, $b) => $a->due_date <=> $b->due_date);

        return array_slice($deadlines, 0, 6);
    }
    
    protected function getAverageScore($siswaId)
    {
        $assignmentScores = AssignmentSubmission::where('siswa_id', $siswaId)
            ->whereNotNull('score')
            ->pluck('score');
            
        $practicalScores = NilaiPraktik::where('siswa_id', $siswaId)
            ->whereNull('criteria_id')
            ->whereNotNull('score')
            ->pluck('score');
            
        $allScores = $assignmentScores->merge($practicalScores);
        
        return $allScores->isNotEmpty() ? round($allScores->avg(), 2) : 0;
    }
    
    protected function getStudentRank($siswaId, $kelasId)
    {
        if (!$kelasId) return '-';

        // Ambil semua user_id di kelas
        $classUcIds = Siswa::where('kelas_id', $kelasId)
            ->whereNull('deleted_at')
            ->pluck('user_id');

        if ($classUcIds->isEmpty()) return '-';

        // Bulk query: avg tugas per siswa
        $avgTugas = AssignmentSubmission::selectRaw('siswa_id, AVG(score) as avg')
            ->whereIn('siswa_id', $classUcIds)
            ->whereNotNull('score')
            ->groupBy('siswa_id')
            ->pluck('avg', 'siswa_id');

        // Bulk query: avg praktik per siswa
        $avgPraktik = NilaiPraktik::selectRaw('siswa_id, AVG(score) as avg')
            ->whereIn('siswa_id', $classUcIds)
            ->whereNull('criteria_id')
            ->whereNotNull('score')
            ->groupBy('siswa_id')
            ->pluck('avg', 'siswa_id');

        // Hitung rata-rata gabungan per siswa
        $scores = $classUcIds->map(function($ucId) use ($avgTugas, $avgPraktik) {
            $vals = array_filter([
                $avgTugas[$ucId] ?? null,
                $avgPraktik[$ucId] ?? null,
            ], fn($v) => $v !== null);
            return ['ucId' => $ucId, 'score' => count($vals) ? array_sum($vals) / count($vals) : 0];
        })->sortByDesc('score')->values();

        $rank = $scores->search(fn($s) => $s['ucId'] == $siswaId);
        return $rank !== false ? $rank + 1 : '-';
    }

    protected function getPendingAssignmentsCount($siswaId, $kelasId)
    {
        return Assignment::where('is_published', true)
            ->where('due_date', '>', now())
            ->where(function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
            })
            ->whereDoesntHave('submissions', fn($q) => $q->where('siswa_id', $siswaId))
            ->count();
    }

    protected function calculateAttendanceRate($siswaId)
    {
        $month = now()->month;
        $year = now()->year;

        $presentDays = Attendance::where('siswa_id', $siswaId)
            ->where('status', 'hadir')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->count();

        $workingDays = $this->getWorkingDays($month, $year);

        return $workingDays > 0 ? round(($presentDays / $workingDays) * 100) : 0;
    }

    protected function getWorkingDays($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            if (!$currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    protected function getNotifications($siswaId, $kelasId)
    {
        $notifications = [];

        $urgentAssignments = Assignment::where('is_published', true)
            ->where(function($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId)
                      ->orWhereNull('kelas_id'); // Include assignments without specific class
            })
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays(2))
            ->whereDoesntHave('submissions', function($query) use ($siswaId) {
                $query->where('siswa_id', $siswaId);
            })
            ->count();

        if ($urgentAssignments > 0) {
            $notifications[] = [
                'type' => 'warning',
                'message' => "Anda memiliki $urgentAssignments tugas yang mendekati deadline!",
                'link' => route('siswa.assignments.index')
            ];
        }

        $todayAttendance = Attendance::where('siswa_id', $siswaId)
            ->whereDate('date', Carbon::today())
            ->exists();

        if (!$todayAttendance && !Carbon::now()->isWeekend()) {
            $notifications[] = [
                'type' => 'info',
                'message' => 'Belum ada catatan absensi hari ini. Pastikan Anda sudah absen!',
                'link' => route('siswa.attendance.index')
            ];
        }

        return $notifications;
    }

    /**
     * Get chart data for dashboard.
     */
    public function getChartData(): JsonResponse
    {
        $siswaId = Auth::id();

        $attendanceData = Attendance::selectRaw('DATE(date) as date, COUNT(*) as count, status')
            ->where('siswa_id', $siswaId)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        $scoreData = AssignmentSubmission::selectRaw('DATE(created_at) as date, AVG(score) as average_score')
            ->where('siswa_id', $siswaId)
            ->whereNotNull('score')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'attendance' => $attendanceData,
            'scores' => $scoreData
        ]);
    }
}
