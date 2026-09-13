<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Subject;
use App\Traits\ResolvesAcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use ResolvesAcademicPeriod;
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
            
        // Re-build query for counts (cannot clone after paginate)
        $countQuery = Attendance::where('type', 'praktik')->whereDate('date', $date);
        if ($practical_id) {
            $countQuery->where('practical_id', $practical_id);
        }
        if ($class !== 'all') {
            $countQuery->whereHas('siswa', function($q) use ($class) {
                $q->where('kelas_id', $class);
            });
        }
        $statusCounts = $countQuery->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();
        $statusCounts = [
            'hadir' => $statusCounts['hadir'] ?? 0,
            'izin'  => $statusCounts['izin']  ?? 0,
            'sakit' => $statusCounts['sakit'] ?? 0,
            'alpha' => $statusCounts['alpha'] ?? 0,
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
            $date      = request('date', null);
            $class     = request('class', 'all');
            $type      = request('type', null);
            $subjectId = request('subject_id', null);

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

            if ($subjectId) {
                $query->where('subject_id', $subjectId);
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
                'attendances', 'date', 'stats', 'classes', 'class', 'subjects', 'type', 'subjectId'
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
     * AJAX: ambil daftar mata pelajaran berdasarkan kelas_id
     * (filter dari class_subjects atau subjects.kelas_id).
     * GET /guru/absensi/subjects-by-kelas?kelas_id=X
     */
    public function subjectsByKelas(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        if (!$kelasId) return response()->json([]);

        // Ambil subjects yang terhubung ke kelas via class_subjects ATAU subjects.kelas_id
        $viaClassSubjects = \Illuminate\Support\Facades\DB::table('class_subjects')
            ->where('class_id', $kelasId)
            ->pluck('subject_id');

        $subjects = Subject::where('is_active', true)
            ->where(function ($q) use ($kelasId, $viaClassSubjects) {
                $q->whereIn('id', $viaClassSubjects)
                  ->orWhere('kelas_id', $kelasId);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type']);

        // Fallback: jika tidak ada mapel spesifik untuk kelas, kembalikan semua mapel aktif
        if ($subjects->isEmpty()) {
            $subjects = Subject::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'type']);
        }

        return response()->json($subjects->map(fn($s) => [
            'id'   => $s->id,
            'name' => $s->name . ($s->code ? ' (' . $s->code . ')' : ''),
            'type' => $s->type,
        ]));
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

        // Cek duplikasi per siswa + tanggal + mata pelajaran
        $siswa = \App\Models\Siswa::findOrFail($request->siswa_id);
        $ucId  = $siswa->user_id;

        $dupQuery = Attendance::where('siswa_id', $ucId)->whereDate('date', $request->date);
        if ($request->subject_id) {
            // Ada mapel — cek duplikat spesifik per mapel
            $dupQuery->where('subject_id', $request->subject_id);
        } else {
            // Tanpa mapel — cek duplikat global (backward compat)
            $dupQuery->whereNull('subject_id');
        }

        if ($dupQuery->exists()) {
            $mapelName = $request->subject_id
                ? (Subject::find($request->subject_id)?->name ?? 'mata pelajaran ini')
                : 'tanggal tersebut';
            return back()->withInput()
                ->with('error', "Absensi siswa ini untuk {$mapelName} pada tanggal tersebut sudah ada.");
        }

        try {
            $attendance = Attendance::create([
                'academic_period_id' => $this->resolvePeriodId($request->kelas_id),
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
                            'academic_period_id' => $this->resolvePeriodId($practical->kelas_id),
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
     * Store multiple attendance records (bulk — satu kelas + satu mapel sekaligus).
     * Format baru: status[siswa_id] = hadir|izin|sakit|alpha, note[siswa_id] = ...
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'date'       => 'required|date|before_or_equal:today',
            'kelas_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'status'     => 'required|array|min:1',
            'status.*'   => 'required|in:hadir,izin,sakit,alpha',
        ], [
            'date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'kelas_id.required'    => 'Kelas wajib dipilih.',
            'subject_id.required'  => 'Mata pelajaran wajib dipilih.',
            'status.required'      => 'Status kehadiran wajib diisi.',
        ]);

        try {
            $periodId     = $this->resolvePeriodId($request->kelas_id);
            $createdCount = 0;
            $skippedCount = 0;

            foreach ($request->status as $siswaId => $statusVal) {
                $siswa = Siswa::find($siswaId);
                if (!$siswa) continue;

                $ucId = $siswa->user_id;

                // Skip jika sudah ada absensi untuk mapel + tanggal yang sama
                $exists = Attendance::where('siswa_id', $ucId)
                    ->whereDate('date', $request->date)
                    ->where('subject_id', $request->subject_id)
                    ->exists();

                if ($exists) { $skippedCount++; continue; }

                Attendance::create([
                    'academic_period_id' => $periodId,
                    'siswa_id'    => $ucId,
                    'kelas_id'    => $request->kelas_id,
                    'subject_id'  => $request->subject_id,
                    'date'        => $request->date,
                    'status'      => $statusVal,
                    'note'        => $request->note[$siswaId] ?? null,
                    'guru_id'     => Auth::id(),
                    'recorded_by' => Auth::id(),
                ]);
                $createdCount++;
            }

            $msg = "Absensi berhasil dicatat untuk {$createdCount} siswa.";
            if ($skippedCount > 0) {
                $msg .= " {$skippedCount} siswa dilewati (sudah diabsen).";
            }

            Log::info('Bulk attendance created', [
                'kelas_id'    => $request->kelas_id,
                'subject_id'  => $request->subject_id,
                'date'        => $request->date,
                'created'     => $createdCount,
                'skipped'     => $skippedCount,
                'guru_id'     => Auth::id(),
            ]);

            return redirect()->route('guru.absensi.index')->with('success', $msg);

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
            'siswa_id'   => 'nullable',   // dikirim sebagai hidden field dari view edit
            'date'       => 'nullable|date', // dikirim sebagai hidden field dari view edit
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
