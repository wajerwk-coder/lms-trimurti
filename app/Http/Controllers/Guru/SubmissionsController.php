<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SubmissionsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'guru']);
    }

    public function index(Request $request): View
    {
        try {
            $guruId = Auth::id();

            $query = AssignmentSubmission::with([
                    'assignment.subject',
                    'assignment.kelas',
                    'siswa',
                ])
                ->whereHas('assignment', fn($q) => $q->where('guru_id', $guruId));

            if ($request->filled('status')) {
                if ($request->status === 'graded') {
                    $query->whereNotNull('score');
                } elseif ($request->status === 'ungraded') {
                    $query->whereNull('score');
                }
            }

            $allSubmissions = $query->latest('created_at')->paginate(15);

            // Stats — single query each
            $baseQuery = fn() => AssignmentSubmission::whereHas('assignment',
                fn($q) => $q->where('guru_id', $guruId)
            );

            $stats = [
                'total_submissions' => $baseQuery()->count(),
                'pending_grading'   => $baseQuery()->whereNull('score')->count(),
                'graded'            => $baseQuery()->whereNotNull('score')->count(),
                'average_score'     => round($baseQuery()->whereNotNull('score')->avg('score') ?? 0, 1),
            ];

            return view('guru.submissions.index', compact('allSubmissions', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Submissions index error: ' . $e->getMessage());
            return view('guru.submissions.index', [
                'allSubmissions' => new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(), 0, 15, 1, ['path' => request()->url()]
                ),
                'stats' => ['total_submissions'=>0,'pending_grading'=>0,'graded'=>0,'average_score'=>0],
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    public function show(AssignmentSubmission $submission): View
    {
        try {
            $this->authorizeSubmission($submission);

            $submission->load([
                'assignment.subject',
                'assignment.kelas',
                'siswa',
            ]);

            return view('guru.submissions.show', compact('submission'));

        } catch (\Exception $e) {
            \Log::error('Submission show error: ' . $e->getMessage());
            return redirect()->route('guru.submissions.index')
                ->with('error', 'Submission tidak ditemukan.');
        }
    }

    public function grade(Request $request, AssignmentSubmission $submission)
    {
        try {
            $this->authorizeSubmission($submission);

            $request->validate([
                'score'    => 'required|numeric|min:0|max:' . ($submission->assignment?->max_score ?? 100),
                'feedback' => 'nullable|string|max:1000',
            ], [
                'score.required' => 'Nilai wajib diisi.',
                'score.max'      => 'Nilai tidak boleh melebihi nilai maksimum.',
            ]);

            $submission->update([
                'score'     => $request->score,
                'feedback'  => $request->feedback,
                'status'    => 'graded',
                'graded_by' => Auth::id(),
                'graded_at' => now(),
            ]);

            return redirect()
                ->route('guru.submissions.show', $submission->id)
                ->with('success', 'Nilai berhasil disimpan.');

        } catch (\Exception $e) {
            \Log::error('Submission grade error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    private function authorizeSubmission(AssignmentSubmission $submission): void
    {
        // Check if the guru has access to this submission
        $guru = Auth::user();
        
        if (!$submission->assignment || 
            $submission->assignment->guru_id !== $guru->id) {
            abort(403, 'Anda tidak memiliki akses ke submission ini.');
        }
    }
}
