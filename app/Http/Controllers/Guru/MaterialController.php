<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialDownload;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
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

        $materials = Material::withCount('downloads')
            ->with('subject')
            ->where('guru_id', $guruId)
            ->latest()
            ->paginate(12);

        // Stats dari query aggregate (bukan filter paginator yang hanya 1 halaman)
        $totalPublished = Material::where('guru_id', $guruId)->whereNotNull('published_at')->count();
        $totalDraft     = Material::where('guru_id', $guruId)->whereNull('published_at')->count();
        $totalDownloads = Material::where('guru_id', $guruId)->sum('downloads_count');

        // Subjects untuk filter (dari tabel subjects)
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('guru.materials.index', compact(
            'materials', 'subjects', 'totalPublished', 'totalDraft', 'totalDownloads'
        ));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create(): View
    {
        $guruId      = Auth::id();
        $guruProfile = Auth::user()->guruProfile;

        $classSubjects = $this->getGuruSubjects($guruId, $guruProfile);

        $classes = DB::table('classes')
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('guru.materials.create', compact('classSubjects', 'classes'));
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'content'    => 'nullable|string',
            'video_url'  => 'nullable|url|max:500',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar|max:40960',
        ], [
            'title.required'      => 'Judul materi wajib diisi.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists'   => 'Mata pelajaran tidak valid.',
            'file.mimes'          => 'Format: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR.',
            'file.max'            => 'Ukuran file maksimal 40 MB.',
            'video_url.url'       => 'URL video tidak valid.',
        ]);

        try {
            $material                  = new Material();
            $material->guru_id         = Auth::id();
            $material->title           = $request->title;
            $material->subject_id      = $request->subject_id;
            // Form pakai class_id atau kelas_id — handle keduanya
            $material->kelas_id        = $request->kelas_id ?? $request->class_id ?? null;
            $material->content         = $request->content;
            $material->video_url       = $request->video_url;
            // Default: auto publish kecuali guru uncheck
            $material->published_at    = $request->has('is_published') && !$request->boolean('is_published')
                ? null
                : now();
            $material->views_count     = 0;
            $material->downloads_count = 0;

            if ($request->hasFile('file')) {
                $file     = $request->file('file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file->getClientOriginalName());

                // Coba upload ke Cloudinary jika dikonfigurasi
                $cloudName = config('cloudinary.cloud_name');
                $apiKey    = config('cloudinary.api_key');
                $apiSecret = config('cloudinary.api_secret');

                if ($cloudName && $apiKey && $apiSecret && $cloudName !== 'aw9h9icb_placeholder') {
                    try {
                        $cloudinary = new \Cloudinary\Cloudinary([
                            'cloud' => [
                                'cloud_name' => $cloudName,
                                'api_key'    => $apiKey,
                                'api_secret' => $apiSecret,
                            ],
                            'url' => ['secure' => true],
                        ]);
                        $result = $cloudinary->uploadApi()->upload(
                            $file->getRealPath(),
                            [
                                'folder'        => 'materials',
                                'resource_type' => 'raw',
                                'public_id'     => $filename,
                            ]
                        );
                        $material->file_url = $result['secure_url'];
                    } catch (\Exception $ce) {
                        Log::warning('Cloudinary upload failed, fallback local: ' . $ce->getMessage());
                        $file->storeAs('materials', $filename, 'public');
                        $material->file_url = $filename;
                    }
                } else {
                    $file->storeAs('materials', $filename, 'public');
                    $material->file_url = $filename;
                }
            }

            $material->save();

            Log::info('Material created', [
                'material_id' => $material->id,
                'guru_id'     => Auth::id(),
            ]);

            return redirect()->route('guru.materials.index')
                ->with('success', "Materi '{$material->title}' berhasil ditambahkan.");

        } catch (\Exception $e) {
            Log::error('Material creation failed: ' . $e->getMessage(), ['guru_id' => Auth::id()]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(Material $material): View
    {
        if ($material->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }

        $downloads = MaterialDownload::with('siswa')
            ->where('material_id', $material->id)
            ->orderByDesc('downloaded_at')
            ->paginate(15);

        $stats = [
            'total_downloads'    => $material->downloads_count ?? 0,
            'last_week_downloads' => MaterialDownload::where('material_id', $material->id)
                ->where('downloaded_at', '>=', now()->subWeek())
                ->count(),
            'unique_downloaders' => MaterialDownload::where('material_id', $material->id)
                ->distinct('siswa_id')
                ->count('siswa_id'),
        ];

        return view('guru.materials.show', compact('material', 'downloads', 'stats'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(Material $material): View
    {
        if ($material->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }

        $guruId      = Auth::id();
        $guruProfile = Auth::user()->guruProfile;
        $classSubjects = $this->getGuruSubjects($guruId, $guruProfile);

        return view('guru.materials.edit', compact('material', 'classSubjects'));
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, Material $material): RedirectResponse
    {
        if ($material->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }

        $request->validate([
            'title'      => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'content'    => 'nullable|string',
            'video_url'  => 'nullable|url|max:500',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar|max:40960',
        ], [
            'title.required'      => 'Judul materi wajib diisi.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists'   => 'Mata pelajaran tidak valid.',
            'file.mimes'          => 'Format: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR.',
            'file.max'            => 'Ukuran file maksimal 40 MB.',
            'video_url.url'       => 'URL video tidak valid.',
        ]);

        try {
            $material->title        = $request->title;
            $material->subject_id   = $request->subject_id;
            $material->kelas_id     = $request->kelas_id ?? $request->class_id ?? $material->kelas_id;
            $material->content      = $request->content;
            $material->video_url    = $request->video_url;
            $material->published_at = $request->has('is_published') && !$request->boolean('is_published')
                ? null
                : ($material->published_at ?? now());

            if ($request->hasFile('file')) {
                $file     = $request->file('file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file->getClientOriginalName());

                $cloudName = config('cloudinary.cloud_name');
                $apiKey    = config('cloudinary.api_key');
                $apiSecret = config('cloudinary.api_secret');

                if ($cloudName && $apiKey && $apiSecret && $cloudName !== 'aw9h9icb_placeholder') {
                    try {
                        $cloudinary = new \Cloudinary\Cloudinary([
                            'cloud' => [
                                'cloud_name' => $cloudName,
                                'api_key'    => $apiKey,
                                'api_secret' => $apiSecret,
                            ],
                            'url' => ['secure' => true],
                        ]);
                        $result = $cloudinary->uploadApi()->upload(
                            $file->getRealPath(),
                            [
                                'folder'        => 'materials',
                                'resource_type' => 'raw',
                                'public_id'     => $filename,
                            ]
                        );
                        $material->file_url = $result['secure_url'];
                    } catch (\Exception $ce) {
                        Log::warning('Cloudinary update upload failed: ' . $ce->getMessage());
                        if ($material->file_url && !str_starts_with($material->file_url, 'http')) {
                            Storage::disk('public')->delete('materials/' . $material->file_url);
                        }
                        $file->storeAs('materials', $filename, 'public');
                        $material->file_url = $filename;
                    }
                } else {
                    if ($material->file_url && !str_starts_with($material->file_url, 'http')) {
                        Storage::disk('public')->delete('materials/' . $material->file_url);
                    }
                    $file->storeAs('materials', $filename, 'public');
                    $material->file_url = $filename;
                }
            }

            $material->save();

            Log::info('Material updated', [
                'material_id' => $material->id,
                'guru_id'     => Auth::id(),
            ]);

            return redirect()->route('guru.materials.index')
                ->with('success', "Materi '{$material->title}' berhasil diperbarui.");

        } catch (\Exception $e) {
            Log::error('Material update failed: ' . $e->getMessage(), ['material_id' => $material->id]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(Material $material): RedirectResponse
    {
        if ($material->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }

        try {
            if ($material->file_url) {
                Storage::disk('public')->delete('materials/' . $material->file_url);
            }
            MaterialDownload::where('material_id', $material->id)->delete();
            $nama = $material->title;
            $material->delete();

            return redirect()->route('guru.materials.index')
                ->with('success', "Materi '{$nama}' berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Material deletion failed: ' . $e->getMessage(), ['material_id' => $material->id]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ── Helper ────────────────────────────────────────────────────────────

    /**
     * Ambil mata pelajaran yang diajar guru via pivot guru_subjects,
     * fallback ke subjects.guru_id, lalu semua jika masih kosong.
     */
    private function getGuruSubjects(int $guruId, $guruProfile): \Illuminate\Support\Collection
    {
        if ($guruProfile) {
            $via = $guruProfile->subjects()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => (object)[
                    'subject_id'   => $s->id,
                    'subject_name' => $s->name,
                    'class_name'   => null,
                ]);
            if ($via->isNotEmpty()) return $via;
        }

        $direct = \App\Models\Subject::where('is_active', true)
            ->where('guru_id', $guruId)
            ->orderBy('name')
            ->get()
            ->map(fn($s) => (object)[
                'subject_id'   => $s->id,
                'subject_name' => $s->name,
                'class_name'   => null,
            ]);
        if ($direct->isNotEmpty()) return $direct;

        return \App\Models\Subject::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($s) => (object)[
                'subject_id'   => $s->id,
                'subject_name' => $s->name,
                'class_name'   => null,
            ]);
    }

    // ── Toggle Publish ────────────────────────────────────────────────────

    public function togglePublish(Material $material): RedirectResponse
    {
        if ($material->guru_id !== Auth::id()) {
            abort(403);
        }

        $wasPublished = $material->published_at !== null;
        $material->published_at = $wasPublished ? null : now();
        $material->save();

        $status = $wasPublished ? 'disembunyikan' : 'diterbitkan';
        return back()->with('success', "Materi '{$material->title}' berhasil {$status}.");
    }

    // ── Download ──────────────────────────────────────────────────────────

    public function download(Material $material)
    {
        if ($material->guru_id !== Auth::id()) {
            abort(403);
        }

        if (!$material->file_url) {
            return back()->with('error', 'File materi tidak ditemukan.');
        }

        // Jika Cloudinary URL → redirect langsung
        if (str_starts_with($material->file_url, 'http')) {
            DB::table('materials')->where('id', $material->id)->increment('downloads_count');
            return redirect($material->file_url);
        }

        // File lokal — coba berbagai format path
        $paths = [
            storage_path('app/public/' . $material->file_url),
            storage_path('app/public/materials/' . $material->file_url),
            storage_path('app/public/materials/' . ltrim($material->file_url, '/')),
        ];

        foreach ($paths as $filePath) {
            if (file_exists($filePath)) {
                DB::table('materials')->where('id', $material->id)->increment('downloads_count');
                $ext          = pathinfo($material->file_url, PATHINFO_EXTENSION);
                $downloadName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $material->title) . '.' . $ext;
                return response()->download($filePath, $downloadName);
            }
        }

        return back()->with('error', 'File tidak ditemukan di server. File lokal hilang setelah redeploy. Silakan upload ulang materi.');
    }

    // ── Bulk actions ──────────────────────────────────────────────────────

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => ['required', Rule::exists('materials', 'id')->where('guru_id', Auth::id())],
        ]);

        try {
            $materials = Material::where('guru_id', Auth::id())->whereIn('id', $request->ids)->get();
            foreach ($materials as $m) {
                if ($m->file_url) Storage::disk('public')->delete('materials/' . $m->file_url);
                MaterialDownload::where('material_id', $m->id)->delete();
                $m->delete();
            }
            return back()->with('success', count($request->ids) . ' materi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bulkPublish(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => ['required', Rule::exists('materials', 'id')->where('guru_id', Auth::id())],
        ]);

        $count = Material::where('guru_id', Auth::id())->whereIn('id', $request->ids)
            ->whereNull('published_at')
            ->update(['published_at' => now()]);

        return back()->with('success', "{$count} materi berhasil diterbitkan.");
    }

    public function bulkUnpublish(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => ['required', Rule::exists('materials', 'id')->where('guru_id', Auth::id())],
        ]);

        $count = Material::where('guru_id', Auth::id())->whereIn('id', $request->ids)
            ->whereNotNull('published_at')
            ->update(['published_at' => null]);

        return back()->with('success', "{$count} materi berhasil disembunyikan.");
    }
}
