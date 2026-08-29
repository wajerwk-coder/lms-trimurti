<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Practical;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PracticalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:guru');
    }

    // ── Index ─────────────────────────────────────────────────────────────

    public function index(): View
    {
        $guruId = Auth::id();

        $praktikums = Practical::with(['subject', 'kelas'])
            ->where('guru_id', $guruId)
            ->withCount('scores')
            ->latest()
            ->paginate(12);

        $totalPublished = Practical::where('guru_id', $guruId)->where('is_published', true)->count();
        $totalDraft     = Practical::where('guru_id', $guruId)->where('is_published', false)->count();

        return view('guru.praktikum.index', compact('praktikums', 'totalPublished', 'totalDraft'));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create(): View
    {
        $guruId      = Auth::id();
        $guruProfile = Auth::user()->guruProfile;

        // ── Mata pelajaran via pivot guru_subjects (prioritas utama) ─────────
        $subjectsViaPivot = collect();
        if ($guruProfile) {
            $subjectsViaPivot = $guruProfile->subjects()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => (object)[
                    'subject_id'   => $s->id,
                    'subject_name' => $s->name,
                    'class_name'   => null,
                ]);
        }

        // ── Fallback 1: subjects.guru_id ──────────────────────────────────────
        if ($subjectsViaPivot->isEmpty()) {
            $subjectsViaPivot = \App\Models\Subject::where('is_active', true)
                ->where('guru_id', $guruId)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => (object)[
                    'subject_id'   => $s->id,
                    'subject_name' => $s->name,
                    'class_name'   => null,
                ]);
        }

        // ── Fallback 2: class_subjects.teacher_id ────────────────────────────
        if ($subjectsViaPivot->isEmpty()) {
            $subjectsViaPivot = DB::table('class_subjects')
                ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
                ->join('classes',  'class_subjects.class_id',   '=', 'classes.id')
                ->where('subjects.is_active', true)
                ->where('class_subjects.teacher_id', $guruId)
                ->select(
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'classes.name as class_name'
                )
                ->orderBy('subjects.name')
                ->get();
        }

        // ── Last resort: semua mapel ──────────────────────────────────────────
        if ($subjectsViaPivot->isEmpty()) {
            $subjectsViaPivot = \App\Models\Subject::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => (object)[
                    'subject_id'   => $s->id,
                    'subject_name' => $s->name,
                    'class_name'   => null,
                ]);
        }

        $classSubjects = $subjectsViaPivot->unique('subject_id')->values();

        // ── Kelas ─────────────────────────────────────────────────────────────
        $classes = DB::table('classes')
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('guru.praktikum.create', compact('classSubjects', 'classes'));
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'instructions' => 'nullable|string',
            'subject_id'   => 'required|exists:subjects,id',
            'kelas_id'     => 'nullable|exists:classes,id',
            'due_date'     => 'required|date|after:now',
        ], [
            'title.required'       => 'Judul praktikum wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'subject_id.required'  => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists'    => 'Mata pelajaran tidak valid.',
            'due_date.after'       => 'Batas waktu harus setelah sekarang.',
        ]);

        try {
            $isPublished = $request->boolean('publish_now');

            $praktikum = Practical::create([
                'guru_id'          => Auth::id(),
                'subject_id'       => $request->subject_id,
                'kelas_id'         => $request->kelas_id ?? null,
                'title'            => $request->title,
                'description'      => $request->description,
                'instructions'     => $request->instructions,
                'due_date'         => $request->due_date,
                'is_published'     => $isPublished,
                'published_at'     => $isPublished ? now() : null,
                'is_active'        => true,
                'views_count'      => 0,
                'submissions_count'=> 0,
            ]);

            Log::info('Practical created', ['id' => $praktikum->id, 'guru_id' => Auth::id()]);

            return redirect()->route('guru.praktikum.index')
                ->with('success', "Praktikum '{$praktikum->title}' berhasil dibuat.");

        } catch (\Exception $e) {
            Log::error('Practical create failed: ' . $e->getMessage(), ['guru_id' => Auth::id()]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(Practical $praktikum): View
    {
        if ($praktikum->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke praktikum ini.');
        }

        $praktikum->load(['subject', 'kelas', 'scores.siswa']);

        $stats = [
            'total_scores'    => $praktikum->scores->count(),
            'average_score'   => round($praktikum->scores->whereNotNull('score')->avg('score') ?? 0, 1),
            'graded_count'    => $praktikum->scores->whereNotNull('score')->count(),
        ];

        return view('guru.praktikum.show', compact('praktikum', 'stats'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(Practical $praktikum): View
    {
        if ($praktikum->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke praktikum ini.');
        }

        $guruId      = Auth::id();
        $guruProfile = Auth::user()->guruProfile;

        // Sama dengan create — gunakan pivot guru_subjects
        $subjectsViaPivot = collect();
        if ($guruProfile) {
            $subjectsViaPivot = $guruProfile->subjects()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => (object)[
                    'subject_id'   => $s->id,
                    'subject_name' => $s->name,
                    'class_name'   => null,
                ]);
        }

        if ($subjectsViaPivot->isEmpty()) {
            $subjectsViaPivot = \App\Models\Subject::where('is_active', true)
                ->where('guru_id', $guruId)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => (object)[
                    'subject_id'   => $s->id,
                    'subject_name' => $s->name,
                    'class_name'   => null,
                ]);
        }

        if ($subjectsViaPivot->isEmpty()) {
            $subjectsViaPivot = \App\Models\Subject::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => (object)[
                    'subject_id'   => $s->id,
                    'subject_name' => $s->name,
                    'class_name'   => null,
                ]);
        }

        $classSubjects = $subjectsViaPivot->unique('subject_id')->values();

        $classes = DB::table('classes')
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('guru.praktikum.edit', compact('praktikum', 'classSubjects', 'classes'));
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, Practical $praktikum): RedirectResponse
    {
        if ($praktikum->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke praktikum ini.');
        }

        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'instructions' => 'nullable|string',
            'subject_id'   => 'required|exists:subjects,id',
            'kelas_id'     => 'nullable|exists:classes,id',
            'due_date'     => 'required|date',
        ], [
            'title.required'       => 'Judul praktikum wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'subject_id.required'  => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists'    => 'Mata pelajaran tidak valid.',
        ]);

        try {
            $isPublished = $request->boolean('publish_now');

            $praktikum->update([
                'subject_id'       => $request->subject_id,
                'kelas_id'         => $request->kelas_id ?? $praktikum->kelas_id,
                'title'            => $request->title,
                'description'      => $request->description,
                'instructions'     => $request->instructions,
                'due_date'         => $request->due_date,
                'is_published'     => $isPublished,
                'published_at'     => $isPublished
                    ? ($praktikum->published_at ?? now())
                    : null,
            ]);

            Log::info('Practical updated', ['id' => $praktikum->id, 'guru_id' => Auth::id()]);

            return redirect()->route('guru.praktikum.index')
                ->with('success', "Praktikum '{$praktikum->title}' berhasil diperbarui.");

        } catch (\Exception $e) {
            Log::error('Practical update failed: ' . $e->getMessage(), ['id' => $praktikum->id]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(Practical $praktikum): RedirectResponse
    {
        if ($praktikum->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke praktikum ini.');
        }

        try {
            $nama = $praktikum->title;
            $praktikum->delete();

            return redirect()->route('guru.praktikum.index')
                ->with('success', "Praktikum '{$nama}' berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Practical delete failed: ' . $e->getMessage(), ['id' => $praktikum->id]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ── Toggle Publish ────────────────────────────────────────────────────

    public function togglePublish(Practical $praktikum): RedirectResponse
    {
        if ($praktikum->guru_id !== Auth::id()) {
            abort(403);
        }

        $newVal = !$praktikum->is_published;
        $praktikum->update([
            'is_published' => $newVal,
            'published_at' => $newVal ? ($praktikum->published_at ?? now()) : null,
        ]);

        $status = $newVal ? 'dipublikasikan' : 'disembunyikan';
        return back()->with('success', "Praktikum '{$praktikum->title}' berhasil {$status}.");
    }
}
