<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Kelas;
use App\Models\Subject;
use App\Models\Siswa;
use App\Models\UserCentral;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendanceControllerNew extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:guru');
    }

    /**
     * Display a listing of attendances with enhanced filtering and performance
     */
    public function index(Request $request): View
    {
        try {
            // Get and validate filters
            $filters = $this->getValidatedFilters($request);
            
            // Build optimized query
            $attendances = $this->buildAttendanceQuery($filters);
            
            // Get statistics
            $stats = $this->calculateAttendanceStats($filters);
            
            // Get filter options
            $filterOptions = $this->getFilterOptions();
            
            // Log for debugging
            Log::info('Guru Attendance Index', [
                'filters' => $filters,
                'total_attendances' => $attendances->total(),
                'current_page' => $attendances->currentPage(),
                'user_id' => auth()->id()
            ]);
            
            return view('guru.absensi-new.index', compact(
                'attendances', 
                'stats', 
                'filterOptions', 
                'filters'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error in attendance index: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return view('guru.absensi-new.index', [
                'attendances' => collect(),
                'stats' => $this->getDefaultStats(),
                'filterOptions' => $this->getFilterOptions(),
                'filters' => $this->getDefaultFilters(),
                'error' => 'Terjadi kesalahan saat memuat data absensi. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Show the form for creating a new attendance record
     */
    public function create(Request $request): View
    {
        try {
            $selectedClass = $request->get('class');
            $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
            
            // Get classes with students
            $classes = Kelas::aktif()
                ->whereHas('students')
                ->orderBy('name')
                ->get(['id', 'name', 'grade']);
            
            // Get students for selected class
            $students = collect();
            if ($selectedClass) {
                $students = Siswa::query()
                    ->where('kelas_id', $selectedClass)->with('kelas')
                    ->orderBy('name')
                    ->get();
            }
            
            // Get subjects
            $subjects = Subject::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
            
            return view('guru.absensi-new.create', compact(
                'classes', 
                'students', 
                'subjects', 
                'selectedClass', 
                'selectedDate'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error in attendance create: ' . $e->getMessage());
            
            return view('guru.absensi-new.create', [
                'classes' => collect(),
                'students' => collect(),
                'subjects' => collect(),
                'selectedClass' => null,
                'selectedDate' => Carbon::today()->format('Y-m-d'),
                'error' => 'Terjadi kesalahan saat memuat form. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'date' => 'required|date|before_or_equal:today',
                'type' => 'required|in:regular,praktik',
                'attendances' => 'required|array',
                'attendances.*.siswa_id' => 'required|exists:users_central,id',
                'attendances.*.status' => 'required|in:hadir,izin,sakit,alpha',
                'attendances.*.keterangan' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $attendancesData = $request->get('attendances');
            $createdCount = 0;

            foreach ($attendancesData as $attendanceData) {
                // Check if attendance already exists
                $existing = Attendance::where('siswa_id', $attendanceData['siswa_id'])
                    ->where('date', $request->get('date'))
                    ->where('subject_id', $request->get('subject_id'))
                    ->first();

                if (!$existing) {
                    Attendance::create([
                        'siswa_id' => $attendanceData['siswa_id'],
                        'date' => $request->get('date'),
                        'status' => $attendanceData['status'],
                        'keterangan' => $attendanceData['keterangan'] ?? null,
                        'subject_id' => $request->get('subject_id'),
                        'type' => $request->get('type'),
                        'recorded_by' => auth()->id(),
                        'waktu_masuk' => $attendanceData['status'] === 'hadir' ? now()->format('H:i:s') : null,
                    ]);
                    $createdCount++;
                }
            }

            Log::info('Attendance created', [
                'count' => $createdCount,
                'class_id' => $request->get('class_id'),
                'subject_id' => $request->get('subject_id'),
                'date' => $request->get('date'),
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('guru.absensi-new.index')
                ->with('success', "Berhasil mencatat {$createdCount} data absensi");

        } catch (\Exception $e) {
            Log::error('Error in attendance store: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data absensi. Silakan coba lagi.');
        }
    }

    /**
     * Show the form for editing the specified attendance
     */
    public function edit(Attendance $attendance): View
    {
        try {
            $this->authorizeAttendance($attendance);
            
            $attendance->load(['siswa.kelas', 'siswa.siswa', 'subject']);
            
            $subjects = Subject::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);

            return view('guru.absensi-new.edit', compact('attendance', 'subjects'));
            
        } catch (\Exception $e) {
            Log::error('Error in attendance edit: ' . $e->getMessage());
            
            return redirect()
                ->route('guru.absensi-new.index')
                ->with('error', 'Data absensi tidak ditemukan atau terjadi kesalahan.');
        }
    }

    /**
     * Update the specified attendance
     */
    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        try {
            $this->authorizeAttendance($attendance);
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:hadir,izin,sakit,alpha',
                'keterangan' => 'nullable|string|max:255',
                'waktu_masuk' => 'nullable|date_format:H:i',
                'waktu_keluar' => 'nullable|date_format:H:i|after:waktu_masuk'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $attendance->update([
                'status' => $request->get('status'),
                'keterangan' => $request->get('keterangan'),
                'waktu_masuk' => $request->get('waktu_masuk') 
                    ? Carbon::parse($request->get('waktu_masuk')) 
                    : null,
                'waktu_keluar' => $request->get('waktu_keluar') 
                    ? Carbon::parse($request->get('waktu_keluar')) 
                    : null,
            ]);

            Log::info('Attendance updated', [
                'attendance_id' => $attendance->id,
                'new_status' => $request->get('status'),
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('guru.absensi-new.index')
                ->with('success', 'Data absensi berhasil diperbarui');

        } catch (\Exception $e) {
            Log::error('Error in attendance update: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data absensi.');
        }
    }

    /**
     * Remove the specified attendance
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        try {
            $this->authorizeAttendance($attendance);
            
            $attendance->delete();

            Log::info('Attendance deleted', [
                'attendance_id' => $attendance->id,
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('guru.absensi-new.index')
                ->with('success', 'Data absensi berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Error in attendance destroy: ' . $e->getMessage());
            
            return redirect()
                ->route('guru.absensi-new.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data absensi.');
        }
    }

    /**
     * Get validated filters from request
     */
    private function getValidatedFilters(Request $request): array
    {
        return [
            'search' => $request->get('search', ''),
            'class' => $request->get('class', 'all'),
            'subject' => $request->get('subject', 'all'),
            'date' => $request->get('date', ''),
            'status' => $request->get('status', 'all'),
            'type' => $request->get('type', 'regular'),
            'page' => $request->get('page', 1)
        ];
    }

    /**
     * Build attendance query with filters
     */
    private function buildAttendanceQuery(array $filters)
    {
        $query = Attendance::with([
            'siswa.kelas',
            'siswa.siswa', 
            'subject'
        ])->whereHas('siswa');

        // Date filter
        if ($filters['date']) {
            $query->whereDate('date', $filters['date']);
        } else {
            // Default to last 30 days
            $query->where('date', '>=', Carbon::now()->subDays(30));
        }

        // Class filter
        if ($filters['class'] !== 'all') {
            $query->whereHas('siswa', function($q) use ($filters) {
                $q->where('kelas_id', $filters['class']);
            });
        }

        // Subject filter
        if ($filters['subject'] !== 'all') {
            $query->where('subject_id', $filters['subject']);
        }

        // Status filter
        if ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Type filter
        $query->where('type', $filters['type']);

        // Search filter
        if ($filters['search']) {
            $query->whereHas('siswa', function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('siswa', function($sq) use ($filters) {
                      $sq->where('nis', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        return $query->orderBy('date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15, ['*'], 'page', $filters['page']);
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateAttendanceStats(array $filters): array
    {
        $query = Attendance::where('type', $filters['type']);

        // Apply same date filter as main query
        if ($filters['date']) {
            $query->whereDate('date', $filters['date']);
        } else {
            $query->where('date', '>=', Carbon::now()->subDays(30));
        }

        // Apply class filter
        if ($filters['class'] !== 'all') {
            $query->whereHas('siswa', function($q) use ($filters) {
                $q->where('kelas_id', $filters['class']);
            });
        }

        // Apply subject filter
        if ($filters['subject'] !== 'all') {
            $query->where('subject_id', $filters['subject']);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = "sakit" THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as alpha
        ')->first();

        return [
            'total' => (int) $stats->total,
            'hadir' => (int) $stats->hadir,
            'izin' => (int) $stats->izin,
            'sakit' => (int) $stats->sakit,
            'alpha' => (int) $stats->alpha,
            'persentase_kehadiran' => $stats->total > 0 
                ? round(($stats->hadir / $stats->total) * 100, 1) 
                : 0
        ];
    }

    /**
     * Get filter options
     */
    private function getFilterOptions(): array
    {
        return [
            'classes' => Kelas::aktif()
                ->whereHas('students')
                ->orderBy('name')
                ->pluck('name', 'id'),
            'subjects' => Subject::where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id'),
            'statuses' => [
                'hadir' => 'Hadir',
                'izin' => 'Izin', 
                'sakit' => 'Sakit',
                'alpha' => 'Alpha'
            ],
            'types' => [
                'regular' => 'Reguler',
                'praktik' => 'Praktik'
            ]
        ];
    }

    /**
     * Get default filters
     */
    private function getDefaultFilters(): array
    {
        return [
            'search' => '',
            'class' => 'all',
            'subject' => 'all', 
            'date' => '',
            'status' => 'all',
            'type' => 'regular',
            'page' => 1
        ];
    }

    /**
     * Get default stats
     */
    private function getDefaultStats(): array
    {
        return [
            'total' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'persentase_kehadiran' => 0
        ];
    }

    /**
     * Authorize attendance access
     */
    private function authorizeAttendance(Attendance $attendance): void
    {
        // Add authorization logic here if needed
        // For now, we'll allow all authenticated guru users
    }
}
