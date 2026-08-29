<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\UserCentral;
use App\Models\Subject;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ── Index ─────────────────────────────────────────────────────────────

    public function index()
    {
        $assignments = Assignment::with(['guru', 'submissions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.assignments.index', compact('assignments'));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.assignments.create', [
            'gurus'    => UserCentral::where('role', 'guru')->where('is_active', true)->orderBy('name')->get(),
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
            'kelas'    => Kelas::orderBy('name')->get(),
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'instructions'=> 'nullable|string',
            'guru_id'     => 'required|exists:users_central,id',
            'subject_id'  => 'required|exists:subjects,id',
            'kelas_id'    => 'nullable|exists:classes,id',
            'due_date'    => 'required|date|after:now',
            'max_score'   => 'required|integer|min:1|max:1000',
            'allow_late'  => 'boolean',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ], [
            'title.required'    => 'Judul tugas wajib diisi.',
            'guru_id.required'  => 'Guru wajib dipilih.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'due_date.after'    => 'Batas waktu harus setelah sekarang.',
            'max_score.max'     => 'Nilai maksimal tidak boleh lebih dari 1000.',
            'attachment.mimes'  => 'Format lampiran harus PDF, DOC, DOCX, PPT, atau PPTX.',
            'attachment.max'    => 'Ukuran lampiran maksimal 10 MB.',
        ]);

        try {
            $data = [
                'title'        => $request->title,
                'description'  => $request->description,
                'instructions' => $request->instructions,
                'guru_id'      => $request->guru_id,
                'subject_id'   => $request->subject_id,
                'kelas_id'     => $request->kelas_id,
                'due_date'     => $request->due_date,
                'max_score'    => $request->max_score,
                'allow_late'   => $request->boolean('allow_late', false),
                'is_published' => $request->boolean('publish_now'),
            ];

            if ($request->hasFile('attachment')) {
                $file     = $request->file('attachment');
                $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('assignments', $filename, 'public');
                $data['file_url'] = $filename;
            }

            $assignment = Assignment::create($data);

            Log::info('Assignment created by admin', ['id' => $assignment->id, 'admin_id' => auth()->id()]);

            return redirect()->route('admin.assignments.index')
                ->with('success', "Tugas '{$assignment->title}' berhasil dibuat.");

        } catch (\Throwable $e) {
            Log::error('Admin assignment create failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal membuat tugas: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(Assignment $assignment)
    {
        $assignment->load(['guru', 'submissions.siswa']);
        return view('admin.assignments.show', compact('assignment'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(Assignment $assignment)
    {
        return view('admin.assignments.edit', [
            'assignment' => $assignment,
            'gurus'      => UserCentral::where('role', 'guru')->where('is_active', true)->orderBy('name')->get(),
            'subjects'   => Subject::where('is_active', true)->orderBy('name')->get(),
            'kelas'      => Kelas::orderBy('name')->get(),
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'instructions'=> 'nullable|string',
            'guru_id'     => 'required|exists:users_central,id',
            'subject_id'  => 'required|exists:subjects,id',
            'kelas_id'    => 'nullable|exists:classes,id',
            'due_date'    => 'required|date',
            'max_score'   => 'required|integer|min:1|max:1000',
            'allow_late'  => 'boolean',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ], [
            'attachment.mimes' => 'Format lampiran harus PDF, DOC, DOCX, PPT, atau PPTX.',
            'attachment.max'   => 'Ukuran lampiran maksimal 10 MB.',
        ]);

        try {
            $data = [
                'title'        => $request->title,
                'description'  => $request->description,
                'instructions' => $request->instructions,
                'guru_id'      => $request->guru_id,
                'subject_id'   => $request->subject_id,
                'kelas_id'     => $request->kelas_id,
                'due_date'     => $request->due_date,
                'max_score'    => $request->max_score,
                'allow_late'   => $request->boolean('allow_late', false),
                // Selalu set is_published dari checkbox — boolean() false saat unchecked
                'is_published' => $request->boolean('publish_now'),
            ];

            if ($request->hasFile('attachment')) {
                if ($assignment->file_url) {
                    Storage::disk('public')->delete('assignments/' . $assignment->file_url);
                }
                $file     = $request->file('attachment');
                $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('assignments', $filename, 'public');
                $data['file_url'] = $filename;
            }

            $assignment->update($data);

            Log::info('Assignment updated by admin', ['id' => $assignment->id, 'admin_id' => auth()->id()]);

            return redirect()->route('admin.assignments.index')
                ->with('success', "Tugas '{$assignment->title}' berhasil diperbarui.");

        } catch (\Throwable $e) {
            Log::error('Admin assignment update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui tugas: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(Assignment $assignment): RedirectResponse
    {
        try {
            if ($assignment->file_url) {
                Storage::disk('public')->delete('assignments/' . $assignment->file_url);
            }
            $nama = $assignment->title;
            $assignment->delete();

            return redirect()->route('admin.assignments.index')
                ->with('success', "Tugas '{$nama}' berhasil dihapus.");

        } catch (\Throwable $e) {
            Log::error('Admin assignment destroy failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus tugas: ' . $e->getMessage());
        }
    }

    // ── Toggle Publish ────────────────────────────────────────────────────

    public function togglePublish(Assignment $assignment): RedirectResponse
    {
        $assignment->update(['is_published' => !$assignment->is_published]);
        $assignment->refresh();
        $status = $assignment->is_published ? 'dipublikasikan' : 'disembunyikan';

        return back()->with('success', "Tugas '{$assignment->title}' berhasil {$status}.");
    }

    // ── Bulk Delete ───────────────────────────────────────────────────────

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'assignment_ids'   => 'required|array',
            'assignment_ids.*' => 'exists:assignments,id',
        ]);

        try {
            $assignments = Assignment::whereIn('id', $request->assignment_ids)->get();
            $deleted = 0;
            foreach ($assignments as $assignment) {
                if ($assignment->file_url) {
                    Storage::disk('public')->delete('assignments/' . $assignment->file_url);
                }
                $assignment->delete();
                $deleted++;
            }

            // Jika AJAX / JSON request → return JsonResponse (konsisten dengan MaterialController)
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$deleted} tugas berhasil dihapus.",
                    'deleted' => $deleted,
                ]);
            }

            return redirect()->route('admin.assignments.index')
                ->with('success', "{$deleted} tugas berhasil dihapus.");

        } catch (\Throwable $e) {
            Log::error('Admin bulk assignment delete failed: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus tugas: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus tugas: ' . $e->getMessage());
        }
    }
}
