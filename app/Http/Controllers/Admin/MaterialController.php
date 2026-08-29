<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialDownload;
use App\Models\Subject;
use App\Models\UserCentral;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of all materials.
     */
    public function index(): View
    {
        $materials = Material::withCount('downloads')
            ->with(['subject', 'teacher'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total_materials' => Material::count(),
            'published_materials' => Material::whereNotNull('published_at')->count(),
            'unpublished_materials' => Material::whereNull('published_at')->count(),
            'total_downloads' => MaterialDownload::count(),
        ];

        return view('admin.materials.index', compact('materials', 'stats'));
    }

    /**
     * Show the form for creating a new material.
     */
    public function create(): View
    {
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $teachers = UserCentral::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();
        $kelas    = Kelas::orderBy('name')->get();

        return view('admin.materials.create', compact('subjects', 'teachers', 'kelas'));
    }

    /**
     * Store a newly created material.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'guru_id'    => 'required|exists:users_central,id',
            'subject_id' => 'required|exists:subjects,id',
            'kelas_id'   => 'nullable|exists:classes,id',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar|max:40960',
            'video_url'  => 'nullable|url',
            'content'    => 'nullable|string',
        ], [
            'title.required'   => 'Judul materi wajib diisi.',
            'guru_id.required' => 'Guru wajib dipilih.',
            'guru_id.exists'   => 'Guru tidak ditemukan.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists'   => 'Mata pelajaran tidak ditemukan.',
            'file.mimes'       => 'Format file harus: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR.',
            'file.max'         => 'Ukuran file maksimal 40 MB.',
            'video_url.url'    => 'URL video tidak valid.',
        ]);

        try {
            $data = [
                'guru_id'      => $request->guru_id,
                'title'        => $request->title,
                'subject_id'   => $request->subject_id,
                'kelas_id'     => $request->kelas_id,
                'content'      => $request->content,
                'video_url'    => $request->video_url,
                'published_at' => $request->boolean('publish_now') ? now() : null,
                'views_count'     => 0,
                'downloads_count' => 0,
            ];

            if ($request->hasFile('file')) {
                $file     = $request->file('file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file->getClientOriginalName());
                $file->storeAs('materials', $filename, 'public');
                $data['file_url'] = $filename;
            }

            $material = Material::create($data);

            Log::info('Material created by admin', [
                'material_id' => $material->id,
                'admin_id'    => Auth::id(),
                'title'       => $material->title,
                'ip'          => $request->ip(),
            ]);

            return redirect()->route('admin.materials.index')
                ->with('success', "Materi '{$material->title}' berhasil ditambahkan.");

        } catch (\Exception $e) {
            Log::error('Material creation failed: ' . $e->getMessage(), ['admin_id' => Auth::id()]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified material.
     */
    public function show(Material $material): View
    {
        $downloads = MaterialDownload::with('siswa')
            ->where('material_id', $material->id)
            ->latest()
            ->paginate(15);

        $stats = [
            'total_downloads' => $material->downloads_count,
            'last_week_downloads' => MaterialDownload::where('material_id', $material->id)
                ->where('created_at', '>=', now()->subWeek())
                ->count(),
            'unique_downloaders' => MaterialDownload::where('material_id', $material->id)
                ->distinct('siswa_id')
                ->count('siswa_id'),
        ];

        return view('admin.materials.show', compact('material', 'downloads', 'stats'));
    }

    /**
     * Show the form for editing the material.
     */
    public function edit(Material $material): View
    {
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $teachers = UserCentral::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();
        $kelas    = Kelas::orderBy('name')->get();

        return view('admin.materials.edit', compact('material', 'subjects', 'teachers', 'kelas'));
    }

    /**
     * Update the specified material.
     */
    public function update(Request $request, Material $material): RedirectResponse
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'guru_id'    => 'required|exists:users_central,id',
            'subject_id' => 'required|exists:subjects,id',
            'kelas_id'   => 'nullable|exists:classes,id',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar|max:40960',
            'video_url'  => 'nullable|url',
            'content'    => 'nullable|string',
        ], [
            'title.required'      => 'Judul materi wajib diisi.',
            'guru_id.required'    => 'Guru wajib dipilih.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'file.mimes'          => 'Format file harus: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR.',
            'file.max'            => 'Ukuran file maksimal 40 MB.',
            'video_url.url'       => 'URL video tidak valid.',
        ]);

        try {
            $data = [
                'guru_id'    => $request->guru_id,
                'title'      => $request->title,
                'subject_id' => $request->subject_id,
                'kelas_id'   => $request->kelas_id,
                'content'    => $request->content,
                'video_url'  => $request->video_url,
            ];

            // Publish / unpublish — pakai boolean() bukan has() agar checkbox unchecked = false
            $publishNow = $request->boolean('publish_now');
            $unpublish  = $request->boolean('unpublish');

            if ($unpublish) {
                $data['published_at'] = null;
            } elseif ($publishNow && !$material->published_at) {
                $data['published_at'] = now();
            }

            if ($request->hasFile('file')) {
                // Hapus file lama
                if ($material->file_url) {
                    Storage::disk('public')->delete('materials/' . $material->file_url);
                }
                $file     = $request->file('file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file->getClientOriginalName());
                $file->storeAs('materials', $filename, 'public');
                $data['file_url'] = $filename;
            }

            $material->update($data);

            Log::info('Material updated by admin', [
                'material_id' => $material->id,
                'admin_id'    => Auth::id(),
                'ip'          => $request->ip(),
            ]);

            return redirect()->route('admin.materials.index')
                ->with('success', "Materi '{$material->title}' berhasil diperbarui.");

        } catch (\Exception $e) {
            Log::error('Material update failed: ' . $e->getMessage(), ['material_id' => $material->id]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified material.
     */
    public function destroy(Material $material): RedirectResponse
    {
        try {
            if ($material->file_url) {
                Storage::disk('public')->delete('materials/' . $material->file_url);
            }

            $material->delete();

            Log::info('Material deleted by admin', [
                'material_id' => $material->id,
                'admin_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return redirect()->route('admin.materials.index')
                ->with('success', 'Materi berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('Material deletion failed by admin: ' . $e->getMessage(), [
                'material_id' => $material->id,
                'admin_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Toggle publish status of material.
     */
    public function togglePublish(Material $material): RedirectResponse
    {
        $material->update([
            'published_at' => $material->published_at ? null : now(),
        ]);

        // Refresh agar nilai published_at terbaru terbaca
        $material->refresh();
        $status = $material->published_at ? 'dipublikasikan' : 'disembunyikan';

        return back()->with('success', "Materi '{$material->title}' berhasil {$status}.");
    }

    /**
     * Bulk delete materials.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|exists:materials,id',
        ]);

        try {
            $materials = Material::whereIn('id', $request->ids)->get();

            foreach ($materials as $material) {
                if ($material->file_url) {
                    Storage::disk('public')->delete('materials/' . $material->file_url);
                }
                $material->delete();
            }

            Log::info('Bulk materials deleted by admin', [
                'count'    => count($request->ids),
                'admin_id' => Auth::id(),
                'ip'       => $request->ip(),
            ]);

            return response()->json(['success' => 'Materi berhasil dihapus massal']);

        } catch (\Exception $e) {
            Log::error('Bulk material deletion failed: ' . $e->getMessage(), ['admin_id' => Auth::id()]);
            return response()->json(['error' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    /**
     * Handle file upload and cleanup (helper internal).
     */
    private function handleFileUpload($file, ?string $oldFilename = null): array
    {
        if ($oldFilename) {
            Storage::disk('public')->delete('materials/' . $oldFilename);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension    = $file->getClientOriginalExtension();
        $filename     = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $originalName) . '.' . $extension;
        $path         = $file->storeAs('materials', $filename, 'public');

        return [
            'file'      => $filename,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $extension,
            'mime_type' => $file->getMimeType(),
        ];
    }
}
