<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Practical;
use App\Models\UserCentral;
use App\Models\Subject;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class PracticalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ── Index ─────────────────────────────────────────────────────────────

    public function index()
    {
        $practicals = Practical::with(['guru', 'subject', 'kelas', 'scores'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Hitung stats dari semua data, bukan hanya halaman ini
        $totalPublished   = Practical::where('is_published', true)->count();
        $totalDraft       = Practical::where('is_published', false)->count();
        $totalPenilaian   = \App\Models\NilaiPraktik::count();

        return view('admin.practicals.index', compact(
            'practicals', 'totalPublished', 'totalDraft', 'totalPenilaian'
        ));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.practicals.create', [
            'gurus'    => UserCentral::where('role', 'guru')->where('is_active', true)->orderBy('name')->get(),
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
            'kelas'    => Kelas::orderBy('name')->get(),
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'instructions' => 'nullable|string',
            'guru_id'      => 'required|exists:users_central,id',
            'subject_id'   => 'required|exists:subjects,id',
            'kelas_id'     => 'nullable|exists:classes,id',
            'due_date'     => 'required|date|after:now',
        ], [
            'title.required'       => 'Judul praktikum wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'guru_id.required'     => 'Guru wajib dipilih.',
            'subject_id.required'  => 'Mata pelajaran wajib dipilih.',
            'due_date.after'       => 'Batas waktu harus setelah sekarang.',
        ]);

        try {
            $isPublished = $request->boolean('publish_now');

            $practical = Practical::create([
                'title'        => $request->title,
                'description'  => $request->description,
                'instructions' => $request->instructions,
                'guru_id'      => $request->guru_id,
                'subject_id'   => $request->subject_id,
                'kelas_id'     => $request->kelas_id,
                'due_date'     => $request->due_date,
                'is_published' => $isPublished,
                'published_at' => $isPublished ? now() : null,
                'is_active'    => true,
            ]);

            Log::info('Practical created by admin', ['id' => $practical->id, 'admin_id' => auth()->id()]);

            return redirect()->route('admin.practicals.index')
                ->with('success', "Praktikum '{$practical->title}' berhasil dibuat.");

        } catch (\Throwable $e) {
            Log::error('Admin practical create failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal membuat praktikum: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(Practical $practical)
    {
        $practical->load(['guru', 'subject', 'kelas', 'scores.siswa']);
        return view('admin.practicals.show', compact('practical'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(Practical $practical)
    {
        return view('admin.practicals.edit', [
            'practical' => $practical,
            'gurus'     => UserCentral::where('role', 'guru')->where('is_active', true)->orderBy('name')->get(),
            'subjects'  => Subject::where('is_active', true)->orderBy('name')->get(),
            'kelas'     => Kelas::orderBy('name')->get(),
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, Practical $practical): RedirectResponse
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'instructions' => 'nullable|string',
            'guru_id'      => 'required|exists:users_central,id',
            'subject_id'   => 'required|exists:subjects,id',
            'kelas_id'     => 'nullable|exists:classes,id',
            'due_date'     => 'required|date',      // no after:now agar bisa simpan data lama
        ], [
            'title.required'       => 'Judul praktikum wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'guru_id.required'     => 'Guru wajib dipilih.',
            'subject_id.required'  => 'Mata pelajaran wajib dipilih.',
        ]);

        try {
            $isPublished = $request->boolean('publish_now');

            $practical->update([
                'title'        => $request->title,
                'description'  => $request->description,
                'instructions' => $request->instructions,
                'guru_id'      => $request->guru_id,
                'subject_id'   => $request->subject_id,
                'kelas_id'     => $request->kelas_id,
                'due_date'     => $request->due_date,
                // Selalu set dari checkbox — boolean() false saat unchecked
                'is_published' => $isPublished,
                'published_at' => $isPublished
                    ? ($practical->published_at ?? now())   // pertahankan tanggal asli jika sudah pernah publish
                    : null,
            ]);

            Log::info('Practical updated by admin', ['id' => $practical->id, 'admin_id' => auth()->id()]);

            return redirect()->route('admin.practicals.index')
                ->with('success', "Praktikum '{$practical->title}' berhasil diperbarui.");

        } catch (\Throwable $e) {
            Log::error('Admin practical update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui praktikum: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(Practical $practical): RedirectResponse
    {
        try {
            $nama = $practical->title;
            $practical->delete();

            return redirect()->route('admin.practicals.index')
                ->with('success', "Praktikum '{$nama}' berhasil dihapus.");

        } catch (\Throwable $e) {
            Log::error('Admin practical destroy failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus praktikum: ' . $e->getMessage());
        }
    }

    // ── Toggle Publish ────────────────────────────────────────────────────

    public function togglePublish(Practical $practical): RedirectResponse
    {
        $newPublished = !$practical->is_published;

        $practical->update([
            'is_published' => $newPublished,
            'published_at' => $newPublished
                ? ($practical->published_at ?? now())
                : null,
        ]);

        $practical->refresh();
        $status = $practical->is_published ? 'dipublikasikan' : 'disembunyikan';

        return back()->with('success', "Praktikum '{$practical->title}' berhasil {$status}.");
    }

    // ── Bulk Delete ───────────────────────────────────────────────────────

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'practical_ids'   => 'required|array',
            'practical_ids.*' => 'exists:practicals,id',
        ]);

        try {
            $count = Practical::whereIn('id', $request->practical_ids)->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} praktikum berhasil dihapus.",
                    'deleted' => $count,
                ]);
            }

            return redirect()->route('admin.practicals.index')
                ->with('success', "{$count} praktikum berhasil dihapus.");

        } catch (\Throwable $e) {
            Log::error('Admin practical bulk delete failed: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus praktikum: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus praktikum: ' . $e->getMessage());
        }
    }
}
