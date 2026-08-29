<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:guru');
    }
    
    /**
     * Display praktik attendance form
     */
    public function praktikAttendance(): View
    {
        $date = request('date', Carbon::today()->format('Y-m-d'));
        $class = request('class', 'all');
        $practical_id = request('practical_id');
        
        // Get available praktik
        $practicals = \App\Models\Practical::where('guru_id', Auth::id())
            ->where('is_active', true)
            ->get();
            
        $query = Attendance::with('siswa.kelas')
            ->where('type', 'praktik')
            ->whereDate('date', $date);
            
        if ($practical_id) {
            $query->where('practical_id', $practical_id);
        }

        if ($class !== 'all') {
            $query->whereHas('siswa', function($q) use ($class) {
                $q->where('kelas_id', $class);
            });
        }

        $attendances = $query->latest()->paginate(25);

        $classes = \App\Models\Kelas::aktif()
            ->whereHas('students')
            ->pluck('name', 'id');
            
        // Get status counts
        $statusCounts = [
            'hadir' => $query->clone()->where('status', 'hadir')->count(),
            'izin' => $query->clone()->where('status', 'izin')->count(),
            'sakit' => $query->clone()->where('status', 'sakit')->count(),
            'alpha' => $query->clone()->where('status', 'alpha')->count(),
        ];

        return view('guru.attendance.praktik', [
            'attendances' => $attendances,
            'classes' => $classes,
            'date' => $date,
            'selectedClass' => $class,
            'statusCounts' => $statusCounts,
            'practicals' => $practicals,
            'practical_id' => $practical_id,
        ]);
    }

    /**
     * Display a listing of attendances.
     */
    public function index(): View
    {
        try {
            $date  = request('date', null);
            $class = request('class', 'all');
            $type  = request('type', null);

            // Filter berdasarkan recorded_by (guru yang mencatat) — lebih reliabel
            // daripada class_subject_id yang sering NULL pada data lama
            $query = Attendance::with(['siswa', 'subject', 'kelas'])
                ->where(function ($q) {
                    $q->where('recorded_by', Auth::id())
                      ->orWhere('guru_id', Auth::id());
                });

            if ($date) {
                $query->whereDate('date', $date);
            } else {
                $query->where('date', '>=', Carbon::now()->subDays(30));
            }

            if ($class !== 'all' && $class) {
                $query->where('kelas_id', $class);
            }

            if ($type) {
                $query->where('status', $type);
            }

            $attendances = $query->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $classes = \App\Models\Kelas::orderBy('name')->pluck('name', 'id');

            $subjects = \App\Models\Subject::where('is_active', true)
                ->orderBy('name')->get();

            // Stats dari query yang sama (bukan paginator)
            $statsQuery = Attendance::where(function ($q) {
                $q->where('recorded_by', Auth::id())
                  ->orWhere('guru_id', Auth::id());
            });

            if ($date) {
                $statsQuery->whereDate('date', $date);
            } else {
                $statsQuery->where('date', '>=', Carbon::now()->subDays(30));
            }

            if ($class !== 'all' && $class) {
                $statsQuery->where('kelas_id', $class);
            }

            $statsData = $statsQuery->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $stats = [
                'total' => $attendances->total(),
                'hadir' => $statsData['hadir'] ?? 0,
                'izin'  => $statsData['izin']  ?? 0,
                'sakit' => $statsData['sakit'] ?? 0,
                'alpha' => $statsData['alpha'] ?? 0,
            ];

            return view('guru.absensi.index', compact(
                'attendances', 'date', 'stats', 'classes', 'class', 'subjects', 'type'
            ));

        } catch (\Exception $e) {
            Log::error('Error in guru attendance index: ' . $e->getMessage());
            return view('guru.absensi.index', [
                'attendances' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'date'        => Carbon::today()->format('Y-m-d'),
                'stats'       => ['total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0],
                'classes'     => collect(),
                'class'       => 'all',
                'subjects'    => collect(),
                'type'        => null,
                'error'       => 'Terjadi kesalahan saat memuat data absensi.',
            ]);
        }
    }

    /**
     * Display detail of a single attendance record.
     */
    public function show(Attendance $absensi): View
    {
        $absensi->load(['siswa', 'kelas', 'subject', 'recorder', 'createdBy']);

        return view('guru.absensi.show', compact('absensi'));
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create(): View
    {
        $classes  = Kelas::orderBy('name')->get(['id', 'name']);
        $subjects = Subject::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('guru.absensi.create', compact('classes', 'subjects'));
    }

    /**
     * AJAX: ambil daftar siswa berdasarkan kelas_id.
     * GET /guru/absensi/siswa-by-kelas?kelas_id=X
     */
    public function siswaByKelas(Request $request)
    {
        $kelasId = $request->get('kelas_id');

        if (!$kelasId) {
            return response()->json([]);
        }

        $siswas = Siswa::with('user')
            ->where('kelas_id', $kelasId)
            ->whereNull('deleted_at')
            ->get()
            ->sortBy(fn($s) => $s->user?->name)
            ->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->user?->name ?? "Siswa #$s->id",
                'nis'  => $s->nis ?? '',
            ])
            ->values();

        return response()->json($siswas);
    }

    /**
     * Store a newly created attendance record.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'siswa_id'   => 'required|exists:siswa,id',
            'kelas_id'   => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date'       => 'required|date|before_or_equal:today',
            'status'     => 'required|in:hadir,izin,sakit,alpha',
            'note'       => 'nullable|string|max:500',
        ], [
            'siswa_id.required'       => 'Siswa wajib dipilih.',
            'date.before_or_equal'    => 'Tanggal tidak boleh melebihi hari ini.',
            'status.required'         => 'Status kehadiran wajib dipilih.',
        ]);

        // Cek duplikasi — tapi cek dari users_central.id
        $siswa = \App\Models\Siswa::findOrFail($request->siswa_id);
        $ucId  = $siswa->user_id;

        if (Attendance::where('siswa_id', $ucId)->whereDate('date', $request->date)->exists()) {
            return back()->withInput()
                ->with('error', 'Absensi untuk siswa ini pada tanggal tersebut sudah ada.');
        }

        try {
            $attendance = Attendance::create([
                'siswa_id'    => $ucId,
                'kelas_id'    => $request->kelas_id,
                'subject_id'  => $request->subject_id,
                'date'        => $request->date,
                'status'      => $request->status,
                'note'        => $request->note,
                'guru_id'     => Auth::id(),
                'recorded_by' => Auth::id(),
            ]);

            Log::info('Attendance created', [
                'attendance_id' => $attendance->id,
                'siswa_id'      => $ucId,
                'date'          => $request->date,
                'status'        => $request->status,
                'guru_id'       => Auth::id(),
            ]);

            return redirect()->route('guru.absensi.index')
                ->with('success', 'Absensi berhasil dicatat.');

        } catch (\Exception $e) {
            Log::error('Attendance creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    
    /**
     * Store praktik attendance for multiple students at once.
     */
    public function storePraktikBatch(Request $request): RedirectResponse
    {
        $request->validate([
            'date'         => 'required|date',
            'practical_id' => 'required|exists:practicals,id',
            'status'       => 'required|array',
            'status.*'     => 'required|in:hadir,izin,sakit,alpha',
            'note'         => 'nullable|array',
            'note.*'       => 'nullable|string|max:255',
        ]);

        try {
            // Verify that the practical belongs to the authenticated teacher
            $practical = \App\Models\Practical::where('id', $request->practical_id)
                ->where('guru_id', Auth::id())
                ->firstOrFail();

            // Get students for this practical's class
            $students = Siswa::whereHas('kelas', function ($q) use ($practical) {
                $q->where('id', $practical->kelas_id);
            })->get();

            foreach ($students as $student) {
                if (isset($request->status[$student->id])) {
                    // Konversi siswa.id → users_central.id
                    $ucId = $student->user_id;
                    Attendance::updateOrCreate(
                        [
                            'siswa_id'     => $ucId,
                            'date'         => $request->date,
                            'type'         => 'praktik',
                            'practical_id' => $request->practical_id,
                        ],
                        [
                            'status'      => $request->status[$student->id],
                            'note'        => $request->note[$student->id] ?? null,
                            'recorded_by' => Auth::id(),
                        ]
                    );
                }
            }

            return redirect()->route('guru.absensi.praktik')
                ->with('success', 'Absensi praktik berhasil disimpan untuk semua siswa.');

        } catch (\Exception $e) {
            Log::error('Error creating batch praktik attendance: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan absensi praktik.');
        }
    }

    /**
     * Show the form for bulk creating attendance records.
     */
    public function bulkCreate(): View
    {
        $classes  = Kelas::orderBy('name')->pluck('name', 'id');
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('guru.absensi.bulk-create', compact('classes', 'subjects'));
    }

    /**
     * Store multiple attendance records (bulk — satu kelas sekaligus).
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'date'       => 'required|date|before_or_equal:today',
            'class'      => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'status'     => 'required|in:hadir,izin,sakit,alpha',
            'note'       => 'nullable|string|max:500',
        ], [
            'date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'class.required'       => 'Kelas wajib dipilih.',
        ]);

        try {
            $siswas       = Siswa::where('kelas_id', $request->class)->whereNull('deleted_at')->get();
            $createdCount = 0;

            foreach ($siswas as $siswa) {
                $ucId = $siswa->user_id;
                // Skip jika sudah ada
                if (Attendance::where('siswa_id', $ucId)->whereDate('date', $request->date)->exists()) {
                    continue;
                }
                Attendance::create([
                    'siswa_id'    => $ucId,
                    'kelas_id'    => $request->class,
                    'subject_id'  => $request->subject_id,
                    'date'        => $request->date,
                    'status'      => $request->status,
                    'note'        => $request->note,
                    'guru_id'     => Auth::id(),
                    'recorded_by' => Auth::id(),
                ]);
                $createdCount++;
            }

            Log::info('Bulk attendance created', [
                'class'         => $request->class,
                'date'          => $request->date,
                'created_count' => $createdCount,
                'guru_id'       => Auth::id(),
            ]);

            return redirect()->route('guru.absensi.index')
                ->with('success', "Absensi massal berhasil dicatat untuk {$createdCount} siswa.");

        } catch (\Exception $e) {
            Log::error('Bulk attendance creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the attendance record.
     */
    public function edit(Attendance $absensi): View
    {
        // Security: Double-check ownership
        if ($absensi->recorded_by !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan mengedit absensi ini.');
        }

        $kelas = Kelas::orderBy('name')->pluck('name', 'id');
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('guru.absensi.edit', compact('absensi', 'kelas', 'subjects'));
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, Attendance $absensi): RedirectResponse
    {
        // Security: Double-check ownership
        if ($absensi->recorded_by !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan mengedit absensi ini.');
        }

        $request->validate([
            'status'     => 'required|in:hadir,izin,sakit,alpha',
            'note'       => 'nullable|string|max:500',
            'kelas_id'   => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        try {
            $absensi->update([
                'status'     => $request->status,
                'note'       => $request->note,
                'kelas_id'   => $request->kelas_id,
                'subject_id' => $request->subject_id,
            ]);

            Log::info('Attendance updated', [
                'attendance_id' => $absensi->id,
                'status'        => $request->status,
                'guru_id'       => Auth::id(),
            ]);

            return redirect()->route('guru.absensi.index')
                ->with('success', 'Absensi berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Attendance update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(Attendance $absensi): RedirectResponse
    {
        // Security: Double-check ownership
        if ($absensi->recorded_by !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan menghapus absensi ini.');
        }

        try {
            $absensi->delete();

            Log::info('Attendance deleted', [
                'attendance_id' => $absensi->id,
                'guru_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return redirect()->route('guru.absensi.index')
                ->with('success', 'Absensi berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('Attendance deletion failed: ' . $e->getMessage(), [
                'attendance_id' => $absensi->id,
                'guru_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

}
