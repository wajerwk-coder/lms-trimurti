<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    /**
     * Display a listing of all assignments (active + expired).
     */
    public function index(Request $request): View
    {
        $ucId   = Auth::id();
        $status = $request->get('status', '');
        $search = $request->get('search', '');

        $query = Assignment::with([
                'submissions' => fn($q) => $q->where('siswa_id', $ucId),
                'subject',
            ])
            ->where('is_published', true)
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($status === 'pending', function ($q) use ($ucId) {
                $q->where('due_date', '>', now())
                  ->whereDoesntHave('submissions', fn($s) => $s->where('siswa_id', $ucId));
            })
            ->when($status === 'submitted', function ($q) use ($ucId) {
                $q->whereHas('submissions', fn($s) => $s->where('siswa_id', $ucId));
            })
            ->when($status === 'graded', function ($q) use ($ucId) {
                $q->whereHas('submissions', fn($s) => $s->where('siswa_id', $ucId)->whereNotNull('score'));
            })
            ->when($status === 'overdue', function ($q) use ($ucId) {
                $q->where('due_date', '<', now())
                  ->whereDoesntHave('submissions', fn($s) => $s->where('siswa_id', $ucId));
            })
            ->orderBy('due_date', 'asc');

        $assignments = $query->paginate(12);

        // Stats dari aggregate query, bukan paginator
        $baseQ = Assignment::where('is_published', true);
        $totalAll   = (clone $baseQ)->count();
        $submitted  = AssignmentSubmission::where('siswa_id', $ucId)->distinct('assignment_id')->count('assignment_id');
        $graded     = AssignmentSubmission::where('siswa_id', $ucId)->whereNotNull('score')->count();
        $overdue    = (clone $baseQ)
            ->where('due_date', '<', now())
            ->whereDoesntHave('submissions', fn($s) => $s->where('siswa_id', $ucId))
            ->count();

        return view('siswa.assignments.index', compact(
            'assignments', 'status', 'search',
            'totalAll', 'submitted', 'graded', 'overdue'
        ));
    }

    /**
     * Display the specified assignment.
     */
    public function show($id): View
    {
        $ucId = Auth::id();

        $assignment = Assignment::with(['guru', 'subject', 'kelas'])
            ->where('is_published', true)
            ->findOrFail($id);

        $submission = AssignmentSubmission::where('assignment_id', $id)
            ->where('siswa_id', $ucId)
            ->first();

        $isExpired  = $assignment->due_date && now()->gt($assignment->due_date);
        $isSubmitted = !is_null($submission);
        $isGraded   = $isSubmitted && $submission->score !== null;

        // canSubmit: belum dikumpulkan ATAU sudah dikumpulkan tapi belum dinilai (boleh edit ulang)
        // tapi deadline belum lewat — atau terlambat tapi diizinkan
        $canSubmit  = !$isGraded && $assignment->is_published
                      && (!$isExpired || $assignment->allow_late);

        return view('siswa.assignments.show', compact(
            'assignment', 'submission', 'isExpired', 'canSubmit', 'isSubmitted', 'isGraded'
        ));
    }

    /**
     * Store a submission for the assignment.
     */
    public function submit(Request $request, $id): RedirectResponse
    {
        // Validasi: minimal file ATAU teks harus diisi
        $request->validate([
            'submission_text' => 'nullable|string|max:5000',
            'file'            => 'nullable|file|mimes:pdf,doc,docx,txt,zip,rar,jpg,jpeg,png|max:5120',
        ]);

        // Server-side: wajib salah satu
        if (!$request->hasFile('file') && empty(trim($request->submission_text ?? ''))) {
            return back()
                ->withInput()
                ->with('error', 'Tugas tidak dapat dikumpulkan kosong. Lampirkan file atau isi catatan terlebih dahulu.');
        }

        $assignment = Assignment::where('is_published', true)->findOrFail($id);
        $ucId       = Auth::id(); // users_central.id

        // Validasi deadline
        $isLate = $assignment->due_date && now()->gt($assignment->due_date);
        if ($isLate && !$assignment->allow_late) {
            return back()->with('error', 'Batas waktu pengumpulan telah berlalu dan pengumpulan terlambat tidak diizinkan.');
        }

        try {
            // Cari submission yang sudah ada berdasarkan siswa_id (users_central)
            $submission = AssignmentSubmission::where('assignment_id', $id)
                ->where('siswa_id', $ucId)
                ->first();

            // Jika sudah dinilai, tidak boleh diubah
            if ($submission && $submission->score !== null) {
                return back()->with('error', 'Tugas sudah dinilai oleh guru dan tidak dapat diubah.');
            }

            // Buat baru jika belum ada
            if (!$submission) {
                $submission = new AssignmentSubmission();
                $submission->assignment_id = $id;
                $submission->student_id    = $ucId; // legacy FK (NOT NULL di migration awal)
                // siswa_id hanya set jika kolom ada
                if (\Illuminate\Support\Facades\Schema::hasColumn('assignment_submissions', 'siswa_id')) {
                    $submission->siswa_id = $ucId;
                }
            } else {
                // Update siswa_id jika kolom ada
                if (\Illuminate\Support\Facades\Schema::hasColumn('assignment_submissions', 'siswa_id')) {
                    $submission->siswa_id = $ucId;
                }
            }

            $submission->submission_text = $request->submission_text;
            // Selalu update submitted_at saat kumpul/edit ulang
            $submission->submitted_at    = now();
            $submission->status          = $isLate ? 'late' : 'submitted';

            // Handle upload file
            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($submission->file_path) {
                    Storage::disk('public')->delete('assignment_submissions/' . $submission->file_path);
                }

                $file     = $request->file('file');
                // Sanitize nama file: hapus spasi & karakter khusus
                $origName = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . $origName;

                $file->storeAs('assignment_submissions', $filename, 'public');

                $submission->file_path = $filename;
                // file_size hanya simpan jika kolom ada di DB
                if (\Illuminate\Support\Facades\Schema::hasColumn('assignment_submissions', 'file_size')) {
                    $submission->file_size = $file->getSize();
                }
                // file_url hanya simpan jika kolom ada di DB
                if (\Illuminate\Support\Facades\Schema::hasColumn('assignment_submissions', 'file_url')) {
                    $submission->file_url = 'assignment_submissions/' . $filename;
                }
            }

            $submission->save();

            Log::info('Assignment submitted', [
                'assignment_id' => $id,
                'siswa_id'      => $ucId,
                'is_late'       => $isLate,
                'has_file'      => $request->hasFile('file'),
                'ip'            => $request->ip(),
            ]);

            $msg = $isLate
                ? 'Tugas berhasil dikumpulkan (terlambat). Menunggu penilaian guru.'
                : 'Tugas berhasil dikumpulkan!';

            return redirect()->route('siswa.assignments.show', $id)->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Error submitting assignment: ' . $e->getMessage(), [
                'assignment_id' => $id,
                'siswa_id'      => $ucId,
                'ip'            => $request->ip(),
            ]);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat mengumpulkan tugas: ' . $e->getMessage());
        }
    }

    /**
     * Display submission history.
     */
    public function history(): View
    {
        $submissions = AssignmentSubmission::with(['assignment', 'assignment.guru'])
            ->where('siswa_id', Auth::id())
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        return view('siswa.assignments.history', compact('submissions'));
    }

    /**
     * Download submission file.
     */
    public function downloadFile($assignment, $submissionId): BinaryFileResponse
    {
        $submission = AssignmentSubmission::where('siswa_id', Auth::id())
            ->findOrFail($submissionId);

        if (!$submission->file_path) {
            abort(404, 'File tidak ditemukan.');
        }

        // Cari file di lokasi yang mungkin
        $possiblePaths = [
            storage_path('app/public/assignment_submissions/' . $submission->file_path),
            storage_path('app/public/' . $submission->file_path),
            public_path('storage/assignment_submissions/' . $submission->file_path),
        ];

        foreach ($possiblePaths as $filePath) {
            if (file_exists($filePath)) {
                return response()->download($filePath, basename($submission->file_path));
            }
        }

        abort(404, 'File tidak ditemukan di server.');
    }

    /**
     * Display archived (expired) assignments.
     */
    public function archived(): View
    {
        $assignments = Assignment::with(['submissions' => function($query) {
            $query->where('siswa_id', Auth::id());
        }])
        ->where('is_published', true)
        ->where('due_date', '<=', now())
        ->orderBy('due_date', 'desc')
        ->paginate(10);

        return view('siswa.assignments.archived', compact('assignments'));
    }

    /**
     * Export current student's visible assignments to CSV.
     */
    public function export()
    {
        $studentId = Auth::id();

        $assignments = Assignment::with(['submissions' => function($q) use ($studentId) {
                $q->where('siswa_id', $studentId);
            }])
            ->where('is_published', true)
            ->orderBy('deadline', 'asc')
            ->limit(1000)
            ->get();

        $filename = 'assignments-student-' . $studentId . '-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($assignments) {
            $handle = fopen('php://output', 'w');
            // Header row
            fputcsv($handle, ['Judul', 'Deskripsi', 'Deadline', 'Status Pengumpulan']);
            foreach ($assignments as $a) {
                $submitted = $a->submissions && $a->submissions->count() > 0 ? 'Terkumpul' : 'Belum';
                fputcsv($handle, [
                    $a->title ?? $a->judul ?? '-',
                    str_replace(["\r","\n"], ' ', (string)($a->description ?? '')),
                    optional($a->deadline)->format('Y-m-d H:i') ?? '-',
                    $submitted,
                ]);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
