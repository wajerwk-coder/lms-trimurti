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
        $assignment = Assignment::with(['guru', 'subject', 'kelas'])
            ->where('is_published', true)
            ->findOrFail($id);

        $submission = AssignmentSubmission::where('assignment_id', $id)
            ->where('siswa_id', Auth::id())
            ->first();

        $isExpired = $assignment->due_date && now() > $assignment->due_date;
        $canSubmit = !$isExpired || $assignment->allow_late;

        return view('siswa.assignments.show', compact(
            'assignment', 'submission', 'isExpired', 'canSubmit'
        ));
    }

    /**
     * Store a submission for the assignment.
     */
    public function submit(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'submission_text' => 'nullable|string|max:5000',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt,zip,rar,jpg,jpeg,png|max:5120', // ✅ Perbaikan: gunakan 'file'
        ]);

        $assignment = Assignment::where('is_published', true)
            ->findOrFail($id);

        // Validasi deadline
        if ($assignment->due_date && now() > $assignment->due_date && !$assignment->allow_late) {
            return back()->with('error', 'Batas waktu pengumpulan telah berlalu.');
        }

        try {
            $submission = AssignmentSubmission::firstOrNew([
                'assignment_id' => $id,
                'siswa_id'      => Auth::id(),
            ]);

            // Jika sudah ada submission dan sudah dinilai, tidak boleh edit
            if ($submission->exists && $submission->score !== null) {
                return back()->with('error', 'Tugas sudah dinilai dan tidak dapat diubah.');
            }

            // student_id (NOT NULL) harus sama dengan siswa_id (users_central.id)
            $submission->student_id      = Auth::id();
            $submission->submission_text = $request->submission_text;

            if ($request->hasFile('file')) { // ✅ Perbaikan: gunakan 'file'
                // Hapus file lama jika ada
                if ($submission->file_path) {
                    Storage::disk('public')->delete('assignment_submissions/' . $submission->file_path);
                }

                $file = $request->file('file'); // ✅ Perbaikan: gunakan 'file'
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('assignment_submissions', $filename, 'public');
                $submission->file_path = $filename;
            }

            // Set submitted_at hanya jika belum ada
            if (!$submission->submitted_at) {
                $submission->submitted_at = now();
            }

            $submission->save();

            Log::info('Assignment submitted successfully', [
                'assignment_id' => $id,
                'siswa_id' => Auth::id(),
                'file_uploaded' => $request->hasFile('file'),
                'ip' => $request->ip()
            ]);

            return redirect()->route('siswa.assignments.show', $id)
                ->with('success', 'Tugas berhasil dikumpulkan!');

        } catch (\Exception $e) {
            Log::error('Error submitting assignment: ' . $e->getMessage(), [
                'assignment_id' => $id,
                'siswa_id' => Auth::id(),
                'ip' => $request->ip()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mengumpulkan tugas.');
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

        $filePath = storage_path('app/public/assignment_submissions/' . $submission->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        return response()->download($filePath, $submission->file_path);
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
