<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialDownload;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    /**
     * Display a listing of materials.
     */
    public function index(Request $request): View
    {
        // Get student's class
        $student = Siswa::where('user_id', Auth::id())->first();
        $kelasId = $student->kelas_id ?? null;

        $query = Material::whereNotNull('published_at')
            ->with(['guru', 'subject', 'downloads' => function($query) {
                $query->where('siswa_id', Auth::id());
            }])
            ->withCount('downloads')
            ->where(function($query) use ($kelasId) {
                if ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                          ->orWhereNull('kelas_id'); // Include materials for all classes
                } else {
                    // If no class assigned, show only materials without specific class
                    $query->whereNull('kelas_id');
                }
            });

        // Apply search filter
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Apply subject filter
        if ($subject = $request->get('subject')) {
            $query->where('subject_id', $subject);
        }

        // Remove category filter — column does not exist in DB

        $materials = $query->latest()->paginate(12);

        // Get subjects for filter
        $subjects = \App\Models\Subject::where('is_active', true)->get();

        // Calculate statistics
        $downloadedCount = MaterialDownload::where('siswa_id', Auth::id())
            ->distinct('material_id')
            ->count();

        $recentCount = Material::whereNotNull('published_at')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $favoriteCount = MaterialDownload::where('siswa_id', Auth::id())
            ->whereHas('material', function($q) {
                $q->whereNotNull('published_at');
            })
            ->count();

        return view('siswa.materials.index', compact(
            'materials', 
            'subjects', 
            'downloadedCount', 
            'recentCount', 
            'favoriteCount'
        ));
    }

    /**
     * Resolve the real storage path for a material file.
     * Handles two storage formats:
     *   1. file_url = "filename.pdf"          → stored at materials/filename.pdf
     *   2. file_url = "materials/filename.pdf" → stored as-is under public disk
     */
    private function resolveFilePath(string $fileUrl): ?string
    {
        // Try as-is first (covers "materials/filename" format)
        if (Storage::disk('public')->exists($fileUrl)) {
            return $fileUrl;
        }

        // Try with materials/ prefix (covers bare "filename" format)
        $withPrefix = 'materials/' . ltrim($fileUrl, '/');
        if (Storage::disk('public')->exists($withPrefix)) {
            return $withPrefix;
        }

        // Strip existing materials/ prefix then re-add (handles double-prefix edge case)
        $stripped = preg_replace('#^materials/#', '', $fileUrl);
        $canonical = 'materials/' . $stripped;
        if (Storage::disk('public')->exists($canonical)) {
            return $canonical;
        }

        return null;
    }

    /**
     * Download the material.
     */
    public function download($id)
    {
        $student = Siswa::where('user_id', Auth::id())->first();
        $kelasId = $student->kelas_id ?? null;

        $material = Material::whereNotNull('published_at')
            ->where(function ($query) use ($kelasId) {
                if ($kelasId) {
                    $query->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
                } else {
                    $query->whereNull('kelas_id');
                }
            })
            ->findOrFail($id);

        if (!$material->file_url) {
            return back()->with('error', 'File materi tidak tersedia.');
        }

        $resolvedPath = $this->resolveFilePath($material->file_url);

        if (!$resolvedPath) {
            Log::warning('Material file not found on disk', [
                'material_id' => $id,
                'file_url'    => $material->file_url,
                'siswa_id'    => Auth::id(),
            ]);
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        // Catat download
        $this->logDownload($material->id);

        DB::table('materials')->where('id', $material->id)->increment('downloads_count');

        $ext      = pathinfo($material->file_url, PATHINFO_EXTENSION);
        $filename = preg_replace('/[^a-zA-Z0-9\-_.]/', '_', $material->title) . '.' . $ext;

        return Storage::disk('public')->download($resolvedPath, $filename);
    }

    /**
     * Track download (AJAX).
     */
    public function trackDownload($id): JsonResponse
    {
        $material = Material::whereNotNull('published_at')
            ->findOrFail($id);

        $this->logDownload($material->id);

        DB::table('materials')
            ->where('id', $material->id)
            ->increment('downloads_count');

        return response()->json([
            'success' => true,
            'download_count' => $material->fresh()->downloads_count
        ]);
    }

    /**
     * Display the specified material.
     */
    public function show($id): View
    {
        // Get student's class
        $student = Siswa::where('user_id', Auth::id())->first();
        $kelasId = $student->kelas_id ?? null;

        $material = Material::whereNotNull('published_at')
            ->with(['guru', 'subject'])
            ->where(function($query) use ($kelasId) {
                if ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                          ->orWhereNull('kelas_id');
                } else {
                    $query->whereNull('kelas_id');
                }
            })
            ->findOrFail($id);

        DB::table('materials')
            ->where('id', $material->id)
            ->increment('views_count');

        $isDownloaded = MaterialDownload::where('material_id', $material->id)
            ->where('siswa_id', Auth::id())
            ->exists();

        // Resolve actual file path for view
        $resolvedFilePath = $material->file_url
            ? $this->resolveFilePath($material->file_url)
            : null;

        return view('siswa.materials.show', compact('material', 'isDownloaded', 'resolvedFilePath'));
    }

    /**
     * Display download history.
     */
    public function history(): View
    {
        $downloads = MaterialDownload::with('material')
            ->where('siswa_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('siswa.materials.history', compact('downloads'));
    }

    /**
     * Search materials.
     */
    public function search(Request $request): View
    {
        // Redirect to index with search parameters
        return redirect()->route('siswa.materials.index', $request->all());
    }

    protected function logDownload($materialId)
    {
        return MaterialDownload::firstOrCreate(
            [
                'material_id' => $materialId,
                'siswa_id' => Auth::id()
            ],
            [
                'downloaded_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        );
    }

    /**
     * Get file information (AJAX).
     */
    public function getFileInfo($id): JsonResponse
    {
        $material = Material::whereNotNull('published_at')->findOrFail($id);

        if (!$material->file_url) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        $resolvedPath = $this->resolveFilePath($material->file_url);

        if (!$resolvedPath) {
            return response()->json(['error' => 'File tidak ditemukan di server'], 404);
        }

        $fileSize = Storage::disk('public')->size($resolvedPath);
        $ext      = pathinfo($material->file_url, PATHINFO_EXTENSION);

        return response()->json([
            'filename'     => basename($material->file_url),
            'size'         => $this->formatFileSize($fileSize),
            'type'         => $ext,
            'download_url' => route('siswa.materials.download', $material->id),
        ]);
    }

    protected function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Display health-related materials.
     */
    public function healthMaterials(): View
    {
        $materials = Material::whereNotNull('published_at')
            ->where(function($query) {
                $query->where('title', 'like', '%kesehatan%')
                      ->orWhere('title', 'like', '%medis%')
                      ->orWhere('title', 'like', '%klinis%')
                      ->orWhere('content', 'like', '%kesehatan%');
            })
            ->with(['guru', 'subject', 'downloads' => function($query) {
                $query->where('siswa_id', Auth::id());
            }])
            ->latest()
            ->paginate(12);

        return view('siswa.materials.health', compact('materials'));
    }
}
