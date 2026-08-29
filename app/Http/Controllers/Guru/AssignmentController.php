<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:guru');
    }

    /**
     * Display a listing of assignments.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'active'); // Default ke tab 'active'
        
        // Base query
        $query = Assignment::with(['submissions' => function($query) {
            $query->select('assignment_id', 'score', 'submitted_at')
                  ->whereNotNull('score');
        }])
        ->withCount([
            'submissions',
            'submissions as graded_count' => function($query) {
                $query->whereNotNull('score');
            },
            'submissions as ungraded_count' => function($query) {
                $query->whereNull('score');
            }
        ])
        ->where('guru_id', Auth::id());
        
        // Apply tab-specific filters
        if ($tab === 'active') {
            // Tugas aktif: yang dipublikasi dan belum lewat deadline atau tanpa deadline
            $query->where('is_published', true)
                  ->where(function($q) {
                      $q->where('due_date', '>', now())
                        ->orWhereNull('due_date');
                  });
        } elseif ($tab === 'history') {
            // Semua tugas untuk riwayat
            // Tidak ada filter tambahan, semua tugas ditampilkan
        }
        
        // Apply additional filters if provided
        if ($request->filled('subject_id')) {
            $query->where('class_subject_id', $request->subject_id);
        }
        
        if ($request->filled('class_id')) {
            $query->whereHas('classSubject', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        
        if ($request->filled('status')) {
            $now = now();
            switch ($request->status) {
                case 'active':
                    $query->where('due_date', '>', $now)
                          ->where('is_published', true);
                    break;
                case 'completed':
                    $query->where('due_date', '<', $now);
                    break;
                case 'draft':
                    $query->where('is_published', false);
                    break;
            }
        }
        
        if ($request->filled('period') && $tab === 'history') {
            switch ($request->period) {
                case 'week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
                case 'semester':
                    $query->where('created_at', '>=', now()->subMonths(6));
                    break;
            }
        }
        
        // Get assignments with pagination
        $assignments = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Calculate statistics for history tab
        if ($tab === 'history') {
            $assignments->getCollection()->transform(function ($assignment) {
                $graded = $assignment->submissions->filter(fn($s) => $s->score !== null);
                $assignment->average_score = $graded->count() > 0 ? round($graded->avg('score'), 1) : null;
                $assignment->completion_rate = $assignment->submissions_count > 0 
                    ? round(($assignment->graded_count / $assignment->submissions_count) * 100, 1)
                    : 0;
                return $assignment;
            });
        }
        
        // Get subjects and stats for filters
        $guruId = Auth::id();
        $subjects = \DB::table('class_subjects')
            ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
            ->where('class_subjects.teacher_id', $guruId)
            ->where('subjects.is_active', true)
            ->select('class_subjects.id', 'subjects.name')
            ->distinct()
            ->get();
            
        $classes = \DB::table('classes')
            ->join('class_subjects', 'classes.id', '=', 'class_subjects.class_id')
            ->where('class_subjects.teacher_id', $guruId)
            ->select('classes.id', 'classes.name')
            ->distinct()
            ->orderBy('classes.name')
            ->get();

        // Fallback: semua kelas jika guru belum terdaftar di class_subjects
        if ($classes->isEmpty()) {
            $classes = \DB::table('classes')
                ->whereNull('deleted_at')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }
        
        // Calculate stats for dashboard
        $totalStats = [
            'total_assignments' => Assignment::where('guru_id', Auth::id())->count(),
            'active_assignments' => Assignment::where('guru_id', Auth::id())
                ->where('is_published', true)
                ->where(function($q) {
                    $q->where('due_date', '>', now())->orWhereNull('due_date');
                })->count(),
            'total_submissions' => AssignmentSubmission::whereHas('assignment', function($q) {
                $q->where('guru_id', Auth::id());
            })->count(),
            'graded_submissions' => AssignmentSubmission::whereHas('assignment', function($q) {
                $q->where('guru_id', Auth::id());
            })->whereNotNull('score')->count(),
        ];

        return view('guru.assignments.index', compact('assignments', 'subjects', 'classes', 'tab', 'totalStats'));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $guruId      = Auth::id();
        $guruProfile = Auth::user()->guruProfile;

        $classSubjects = $this->getGuruSubjects($guruId, $guruProfile);

        $classes = \DB::table('classes')
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('guru.assignments.create', compact('classSubjects', 'classes'));
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'instructions' => 'nullable|string',
            'class_id'     => 'required|exists:classes,id',
            'subject_id'   => 'required|exists:subjects,id',
            'file'         => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt,zip,rar|max:20480',
            'deadline'     => 'required|date|after:now',
            'max_score'    => 'required|numeric|min:1|max:1000',
        ], [
            'class_id.required'   => 'Kelas wajib dipilih.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists'   => 'Mata pelajaran yang dipilih tidak valid.',
            'deadline.after'      => 'Batas waktu harus setelah sekarang.',
            'file.max'            => 'Ukuran file maksimal 20 MB.',
        ]);

        try {
            $assignment = new Assignment();
            $assignment->guru_id      = Auth::id();
            $assignment->title        = $request->title;
            $assignment->description  = $request->description;
            $assignment->instructions = $request->instructions;
            $assignment->due_date     = $request->deadline;
            $assignment->max_score    = $request->max_score;
            $assignment->subject_id   = $request->subject_id;
            $assignment->kelas_id     = $request->class_id;
            $assignment->allow_late   = $request->boolean('allow_late');
            $assignment->is_published = $request->boolean('is_published');

            if ($request->hasFile('file')) {
                $fileData = $this->handleFileUpload($request->file('file'));
                $assignment->fill($fileData);
            }

            $assignment->save();

            Log::info('Assignment created', [
                'assignment_id' => $assignment->id,
                'guru_id'       => Auth::id(),
                'title'         => $assignment->title,
                'ip'            => $request->ip(),
            ]);

            return redirect()->route('guru.assignments.index')
                ->with('success', "Tugas '{$assignment->title}' berhasil ditambahkan.");

        } catch (\Exception $e) {
            Log::error('Assignment creation failed: ' . $e->getMessage(), ['guru_id' => Auth::id()]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified assignment.
     */
    public function show(Assignment $assignment)
    {
        if ($assignment->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke tugas ini.');
        }

        $submissions = AssignmentSubmission::with(['siswa', 'assignment'])
            ->where('assignment_id', $assignment->id)
            ->latest()
            ->paginate(15);

        // Alias yang diharapkan view lama
        $recentSubmissions = $submissions->getCollection()->take(5);

        $gradedSubmissions = $submissions->getCollection()->filter(fn($s) => $s->score !== null);

        $stats = [
            'total_submissions' => $submissions->total(),
            'graded_count'      => $gradedSubmissions->count(),
            'average_score'     => round($gradedSubmissions->avg('score') ?: 0, 2),
        ];

        return view('guru.assignments.show', compact('assignment', 'submissions', 'recentSubmissions', 'stats'));
    }

    /**
     * Show the form for editing the assignment.
     */
    public function edit(Assignment $assignment)
    {
        if ($assignment->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke tugas ini.');
        }

        $guruId      = Auth::id();
        $guruProfile = Auth::user()->guruProfile;

        $classSubjects = $this->getGuruSubjects($guruId, $guruProfile);

        $classes = \DB::table('classes')
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('guru.assignments.edit', compact('assignment', 'classSubjects', 'classes'));
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        if ($assignment->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke tugas ini.');
        }

        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'instructions' => 'nullable|string',
            'subject_id'   => 'required|exists:subjects,id',
            'file'         => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt,zip,rar|max:20480',
            'deadline'     => 'required|date',
            'max_score'    => 'required|numeric|min:1|max:1000',
        ], [
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists'   => 'Mata pelajaran tidak valid.',
            'deadline.required'   => 'Batas waktu wajib diisi.',
            'file.max'            => 'Ukuran file maksimal 20 MB.',
        ]);

        try {
            $assignment->title          = $request->title;
            $assignment->description    = $request->description;
            $assignment->instructions   = $request->instructions;
            $assignment->due_date       = $request->deadline;
            $assignment->max_score      = $request->max_score;
            $assignment->subject_id     = $request->subject_id;
            $assignment->kelas_id       = $request->class_id ?? $assignment->kelas_id;
            $assignment->allow_late     = $request->boolean('allow_late');
            $assignment->is_published   = $request->boolean('is_published');

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($assignment->file) {
                    Storage::disk('public')->delete('assignments/' . $assignment->file);
                }
                $fileData = $this->handleFileUpload($request->file('file'));
                $assignment->fill($fileData);
            }

            $assignment->save();

            Log::info('Assignment updated', [
                'assignment_id' => $assignment->id,
                'guru_id'       => Auth::id(),
                'title'         => $assignment->title,
                'ip'            => $request->ip(),
            ]);

            return redirect()->route('guru.assignments.index')
                ->with('success', "Tugas '{$assignment->title}' berhasil diperbarui.");

        } catch (\Exception $e) {
            Log::error('Assignment update failed: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
                'guru_id'       => Auth::id(),
            ]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(Assignment $assignment): RedirectResponse
    {
        if ($assignment->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke tugas ini.');
        }

        try {
            if ($assignment->file) {
                Storage::disk('public')->delete('assignments/' . $assignment->file);
            }

            $nama = $assignment->title;
            $assignment->delete();

            return redirect()->route('guru.assignments.index')
                ->with('success', "Tugas '{$nama}' berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Assignment deletion failed: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
                'guru_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Grade a submission (via gradeSubmission route).
     */
    public function gradeSubmission(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $assignment = $submission->assignment;

        if ($assignment->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $request->validate([
            'score'    => 'required|numeric|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string|max:1000',
        ], [
            'score.required' => 'Nilai wajib diisi.',
            'score.max'      => 'Nilai tidak boleh melebihi nilai maksimal (' . $assignment->max_score . ').',
        ]);

        try {
            $submission->update([
                'score'      => $request->score,
                'feedback'   => $request->feedback,
                'status'     => 'graded',
                'graded_by'  => Auth::id(),
                'graded_at'  => now(),
            ]);
            $assignment->touch();

            return back()->with('success', 'Nilai berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('gradeSubmission failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Toggle publish status of assignment.
     */
    public function togglePublish(Assignment $assignment): RedirectResponse
    {
        if ($assignment->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke tugas ini.');
        }

        $assignment->update(['is_published' => !$assignment->is_published]);
        $assignment->refresh();
        $status = $assignment->is_published ? 'dipublikasikan' : 'disembunyikan';

        return back()->with('success', "Tugas '{$assignment->title}' berhasil {$status}.");
    }

    /**
     * Show submissions for a specific assignment.
     */
    public function submissions(Assignment $assignment)
    {
        if ($assignment->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke tugas ini.');
        }

        $submissions = AssignmentSubmission::with(['siswa', 'assignment'])
            ->where('assignment_id', $assignment->id)
            ->latest()
            ->paginate(15);

        $stats = [
            'total_submissions' => $submissions->total(),
            'graded_count'      => $submissions->getCollection()->filter(fn($s) => $s->score !== null)->count(),
            'average_score'     => round($submissions->getCollection()->filter(fn($s) => $s->score !== null)->avg('score') ?: 0, 2),
        ];

        return view('guru.assignments.submissions', compact('assignment', 'submissions', 'stats'));
    }

    /**
     * Grade a specific submission (via grade route with assignment + submission params).
     */
    public function grade(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        if ($assignment->guru_id !== Auth::id() || $submission->assignment_id !== $assignment->id) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $request->validate([
            'score'    => 'required|numeric|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string|max:1000',
        ], [
            'score.required' => 'Nilai wajib diisi.',
            'score.max'      => 'Nilai tidak boleh melebihi nilai maksimal (' . $assignment->max_score . ').',
        ]);

        try {
            $submission->update([
                'score'     => $request->score,
                'feedback'  => $request->feedback,
                'status'    => 'graded',
                'graded_by' => Auth::id(),
                'graded_at' => now(),
            ]);
            $assignment->touch();

            return back()->with('success', 'Nilai berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('grade failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    /**
     * Get mata pelajaran yang diajarkan guru (via pivot guru_subjects, fallback ke subjects.guru_id).
     */
    private function getGuruSubjects(int $guruId, $guruProfile): \Illuminate\Support\Collection
    {
        // Prioritas 1: pivot guru_subjects
        if ($guruProfile) {
            $via = $guruProfile->subjects()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => (object)[
                    'subject_id'   => $s->id,
                    'subject_name' => $s->name,
                    'class_id'     => null,
                ]);
            if ($via->isNotEmpty()) return $via;
        }

        // Prioritas 2: subjects.guru_id
        $direct = \App\Models\Subject::where('is_active', true)
            ->where('guru_id', $guruId)
            ->orderBy('name')
            ->get()
            ->map(fn($s) => (object)[
                'subject_id'   => $s->id,
                'subject_name' => $s->name,
                'class_id'     => null,
            ]);
        if ($direct->isNotEmpty()) return $direct;

        // Last resort: semua
        return \App\Models\Subject::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($s) => (object)[
                'subject_id'   => $s->id,
                'subject_name' => $s->name,
                'class_id'     => null,
            ]);
    }

    /**
     * Handle file upload and cleanup.
     */
    private function handleFileUpload($file, $oldFilename = null)
    {
        // Delete old file if exists
        if ($oldFilename) {
            Storage::disk('public')->delete('assignments/' . $oldFilename);
        }

        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $path = $file->storeAs('assignments', $filename, 'public');

        return [
            'file' => $filename,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientOriginalExtension(),
        ];
    }
}
