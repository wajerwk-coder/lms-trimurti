<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Attendance::with(['siswa', 'kelas', 'subject']);

            // Filter by date range
            if ($request->filled('start_date')) {
                $query->whereDate('date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('date', '<=', $request->end_date);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by student
            if ($request->filled('siswa_id')) {
                $query->where('siswa_id', $request->siswa_id);
            }

            // Filter by class
            if ($request->filled('kelas_id')) {
                $query->where('kelas_id', $request->kelas_id);
            }

            $attendances = $query->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Get students and classes for filter dropdown
            // Siswa tidak punya kolom 'name' — join ke users_central
            $students = Siswa::with('user')
                ->whereNull('siswa.deleted_at')
                ->join('users_central', 'siswa.user_id', '=', 'users_central.id')
                ->orderBy('users_central.name')
                ->select('siswa.*')
                ->get();
            $kelas = Kelas::orderBy('name')->get();

            // Get statistics
            $stats = $this->getAttendanceStats($request);

            return view('admin.attendance.index', compact('attendances', 'students', 'kelas', 'stats'));
        } catch (\Exception $e) {
            return view('admin.attendance.index', [
                'attendances' => new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(), 0, 20, 1, ['path' => request()->url()]
                ),
                'students' => collect(),
                'kelas'    => collect(),
                'stats'    => [
                    'total' => 0, 'hadir' => 0, 'izin' => 0,
                    'sakit' => 0, 'alpha' => 0, 'attendance_rate' => 0,
                ],
                'error' => 'Gagal memuat data absensi: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Load siswa dengan user (untuk menampilkan nama)
            $students = \App\Models\Siswa::with('user')->whereNull('deleted_at')
                ->orderBy('id')->get();
            $kelas    = Kelas::orderBy('name')->get();
            $subjects = Subject::where('is_active', true)->orderBy('name')->get();

            return view('admin.attendance.create', compact('students', 'kelas', 'subjects'));
        } catch (\Exception $e) {
            return view('admin.attendance.create', [
                'students' => collect(), 'kelas' => collect(), 'subjects' => collect(),
                'error'    => 'Error loading data: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id'   => 'required|exists:siswa,id',
            'kelas_id'   => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date'       => 'required|date',
            'status'     => 'required|in:hadir,izin,sakit,alpha',
            'note'       => 'nullable|string|max:500',
        ], [
            'siswa_id.required' => 'Siswa wajib dipilih.',
            'siswa_id.exists'   => 'Siswa tidak ditemukan.',
            'date.required'     => 'Tanggal wajib diisi.',
            'status.required'   => 'Status kehadiran wajib dipilih.',
        ]);

        try {
            // siswa_id di tabel absensi menyimpan users_central.id
            $siswa = \App\Models\Siswa::findOrFail($request->siswa_id);
            $ucId  = $siswa->user_id;

            Attendance::create([
                'siswa_id'    => $ucId,
                'kelas_id'    => $request->kelas_id,
                'subject_id'  => $request->subject_id,
                'date'        => $request->date,
                'status'      => $request->status,
                'note'        => $request->note,
                'created_by'  => Auth::id(),
                'recorded_by' => Auth::id(),
            ]);

            return redirect()->route('admin.attendance.index')
                ->with('success', 'Data absensi berhasil ditambahkan.');

        } catch (\Throwable $e) {
            \Log::error('Admin attendance store failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['siswa']);
        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        $students = \App\Models\Siswa::with('user')->whereNull('deleted_at')->orderBy('id')->get();
        $kelas    = Kelas::orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        // Temukan siswa yang sesuai: attendance.siswa_id = users_central.id → cari siswa.user_id
        $currentSiswaId = null;
        foreach ($students as $s) {
            if ($s->user_id == $attendance->siswa_id) {
                $currentSiswaId = $s->id;
                break;
            }
        }

        return view('admin.attendance.edit', compact(
            'attendance', 'students', 'kelas', 'subjects', 'currentSiswaId'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'siswa_id'   => 'required|exists:siswa,id',
            'kelas_id'   => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date'       => 'required|date',
            'status'     => 'required|in:hadir,izin,sakit,alpha',
            'note'       => 'nullable|string|max:500',
        ]);

        try {
            // Konversi siswa.id → users_central.id
            $siswa = \App\Models\Siswa::findOrFail($request->siswa_id);
            $ucId  = $siswa->user_id;

            $attendance->update([
                'siswa_id'   => $ucId,
                'kelas_id'   => $request->kelas_id,
                'subject_id' => $request->subject_id,
                'date'       => $request->date,
                'status'     => $request->status,
                'note'       => $request->note,
            ]);

            return redirect()->route('admin.attendance.index')
                ->with('success', 'Data absensi berhasil diperbarui.');

        } catch (\Throwable $e) {
            \Log::error('Admin attendance update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui absensi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        try {
            $attendance->delete();
            return redirect()->route('admin.attendance.index')
                ->with('success', 'Data absensi berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus absensi: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update attendance status
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'attendance_ids'   => 'required|array',
            'attendance_ids.*' => 'exists:attendances,id',
            'status'           => 'required|in:hadir,izin,sakit,alpha',
            'note'             => 'nullable|string|max:500',
        ]);

        try {
            Attendance::whereIn('id', $request->attendance_ids)
                ->update([
                    'status' => $request->status,
                    'note'   => $request->note,
                ]);

            return redirect()->route('admin.attendance.index')
                ->with('success', count($request->attendance_ids) . ' data absensi berhasil diperbarui.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui absensi: ' . $e->getMessage());
        }
    }

    /**
     * Get attendance statistics
     */
    private function getAttendanceStats($request)
    {
        $query = Attendance::query();

        // Apply same filters as main query
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        $total = $query->count();
        $hadir = (clone $query)->where('status', 'hadir')->count();
        $izin = (clone $query)->where('status', 'izin')->count();
        $sakit = (clone $query)->where('status', 'sakit')->count();
        $alpha = (clone $query)->where('status', 'alpha')->count();

        return [
            'total' => $total,
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'attendance_rate' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0
        ];
    }
}
