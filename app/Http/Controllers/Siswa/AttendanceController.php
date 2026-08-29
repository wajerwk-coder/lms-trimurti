<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    /**
     * Display a listing of attendances for the month.
     */
    public function index(Request $request): View
    {
        $siswaId = Auth::id();

        // ✅ Validasi dan sanitize input
        $month = min(max(1, (int)$request->input('month', Carbon::now()->month)), 12);
        $year = max(2000, min((int)$request->input('year', Carbon::now()->year), 2100));

        $attendances = Attendance::with(['subject'])
            ->where('siswa_id', $siswaId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->paginate(20);

        $monthlyStats = $this->getMonthlyStats($siswaId, $month, $year);
        $totalStats = $this->getTotalStats($siswaId);

        return view('siswa.absensi.index', compact('attendances', 'monthlyStats', 'totalStats', 'month', 'year'));
    }

    protected function getMonthlyStats($siswaId, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year  = $year  ?? Carbon::now()->year;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth();

        $stats = Attendance::selectRaw('status, COUNT(*) as count')
            ->where('siswa_id', $siswaId)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        $total       = $stats->sum('count');
        $present     = $stats->firstWhere('status', 'hadir')?->count ?? 0;
        $izin        = $stats->firstWhere('status', 'izin')?->count  ?? 0;
        $sakit       = $stats->firstWhere('status', 'sakit')?->count ?? 0;
        $alpha       = $stats->firstWhere('status', 'alpha')?->count ?? 0;
        $workingDays = $this->getWorkingDays($month, $year);

        return [
            'total'           => $total,
            'present'         => $present,
            'izin'            => $izin,
            'sakit'           => $sakit,
            'absent'          => $alpha,
            'permission'      => $izin + $sakit,   // backward compat
            'percentage'      => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            'breakdown'       => $stats,
            'working_days'    => $workingDays,
            'attendance_rate' => $workingDays > 0 ? round(($present / $workingDays) * 100, 1) : 0,
        ];
    }

    protected function getTotalStats($siswaId)
    {
        $stats = Attendance::selectRaw('status, COUNT(*) as count')
            ->where('siswa_id', $siswaId)
            ->groupBy('status')
            ->get();

        $total   = $stats->sum('count');
        $present = $stats->firstWhere('status', 'hadir')?->count ?? 0;
        $izin    = $stats->firstWhere('status', 'izin')?->count  ?? 0;
        $sakit   = $stats->firstWhere('status', 'sakit')?->count ?? 0;
        $alpha   = $stats->firstWhere('status', 'alpha')?->count ?? 0;

        return [
            'total'      => $total,
            'present'    => $present,
            'izin'       => $izin,
            'sakit'      => $sakit,
            'absent'     => $alpha,
            'permission' => $izin + $sakit,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            'breakdown'  => $stats,
        ];
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

    /**
     * Display the specified attendance record.
     */
    public function show($id): View
    {
        $attendance = Attendance::where('siswa_id', Auth::id())
            ->findOrFail($id);

        return view('siswa.absensi.show', compact('attendance'));
    }

    /**
     * Export attendance report.
     */
    public function export(Request $request): View
    {
        $siswaId = Auth::id();
        $month = min(max(1, (int)$request->input('month', Carbon::now()->month)), 12);
        $year = max(2000, min((int)$request->input('year', Carbon::now()->year), 2100));

        $attendances = Attendance::with(['subject'])
            ->where('siswa_id', $siswaId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'asc')
            ->get();

        $stats = $this->getMonthlyStats($siswaId, $month, $year);

        Log::info('Attendance report exported', [
            'siswa_id' => $siswaId,
            'month' => $month,
            'year' => $year,
            'ip' => $request->ip()
        ]);

        return view('siswa.absensi.export', compact('attendances', 'stats', 'month', 'year'));
    }

    /**
     * Get attendance data in JSON format (API).
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $siswaId = Auth::id();
        $month = min(max(1, (int)$request->input('month', Carbon::now()->month)), 12);
        $year = max(2000, min((int)$request->input('year', Carbon::now()->year), 2100));

        $attendances = Attendance::with(['subject'])
            ->where('siswa_id', $siswaId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->get();

        $stats = $this->getMonthlyStats($siswaId, $month, $year);

        Log::info('Attendance data accessed via API', [
            'siswa_id' => $siswaId,
            'month' => $month,
            'year' => $year,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'attendances' => $attendances,
            'stats' => $stats,
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Display medical records (sick/permission attendance).
     */
    public function medicalRecords(): View
    {
        $siswaId = Auth::id();

        $medicalRecords = Attendance::where('siswa_id', $siswaId)
            ->whereIn('status', ['sakit', 'izin'])
            ->whereNotNull('note')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('siswa.absensi.medical', compact('medicalRecords'));
    }
}
