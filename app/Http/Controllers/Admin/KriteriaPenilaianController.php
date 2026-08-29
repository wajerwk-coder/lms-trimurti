<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KriteriaPenilaian;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KriteriaPenilaianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ── Index ─────────────────────────────────────────────────────────────

    public function index(): View
    {
        if (!Schema::hasTable('assessment_criteria')) {
            return view('admin.kriteria-penilaian.index', [
                'kriteria' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'error'    => 'Tabel assessment_criteria belum ada. Jalankan: php artisan migrate',
            ]);
        }

        try {
            $kriteria = KriteriaPenilaian::orderBy('kategori')
                                        ->orderBy('weight', 'desc')
                                        ->paginate(20);

            return view('admin.kriteria-penilaian.index', compact('kriteria'));

        } catch (\Throwable $e) {
            Log::error('KriteriaPenilaianController::index: ' . $e->getMessage());
            return view('admin.kriteria-penilaian.index', [
                'kriteria' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'error'    => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    // ── Create (single) ───────────────────────────────────────────────────

    public function create(): View
    {
        return view('admin.kriteria-penilaian.create', [
            'kategoriList'     => KriteriaPenilaian::getKategoriList(),
            'tingkatKelasList' => KriteriaPenilaian::getTingkatKelasList(),
            'subjects'         => MataPelajaran::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    // ── Create (combined 4 kategori) ─────────────────────────────────────

    public function createCombined(): View
    {
        return view('admin.kriteria-penilaian.create-combined', [
            'kategoriList'     => KriteriaPenilaian::getKategoriList(),
            'tingkatKelasList' => KriteriaPenilaian::getTingkatKelasList(),
            'subjects'         => MataPelajaran::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    // ── Store (single) ────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'kategori'          => 'required|in:persiapan,pelaksanaan,hasil,sikap',
            'weight'            => 'required|integer|min:1|max:100',
            'description'       => 'nullable|string',
            'mata_praktik'      => 'required|string|max:255',
            'tingkat_kelas'     => 'required|in:X,XI,XII',
            'sop_checklist'     => 'required|array|min:1',
            'sop_checklist.*'   => 'required|string|max:255',
        ], [
            'name.required'         => 'Nama kriteria wajib diisi.',
            'kategori.required'     => 'Kategori wajib dipilih.',
            'weight.required'       => 'Bobot wajib diisi.',
            'mata_praktik.required' => 'Mata praktik wajib dipilih.',
            'tingkat_kelas.required'=> 'Tingkat kelas wajib dipilih.',
            'sop_checklist.required'=> 'Minimal satu item SOP checklist wajib diisi.',
        ]);

        try {
            KriteriaPenilaian::create([
                'name'          => $request->name,
                'kategori'      => $request->kategori,
                'weight'        => $request->weight,
                'description'   => $request->description,
                'mata_praktik'  => $request->mata_praktik,
                'tingkat_kelas' => $request->tingkat_kelas,
                'sop_checklist' => array_values(array_filter($request->sop_checklist)),
                'is_active'     => $request->boolean('is_active', true),
            ]);

            return redirect()->route('admin.kriteria-penilaian.index')
                ->with('success', "Kriteria '{$request->name}' berhasil ditambahkan.");

        } catch (\Throwable $e) {
            Log::error('KriteriaPenilaianController::store: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan kriteria: ' . $e->getMessage());
        }
    }

    // ── Store combined (4 kategori sekaligus) ────────────────────────────

    public function storeCombined(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_praktik'                       => 'required|string|max:255',
            'tingkat_kelas'                      => 'required|in:X,XI,XII',
            'categories'                         => 'required|array',
            'categories.*.name'                  => 'required|string|max:255',
            'categories.*.weight'                => 'required|integer|min:1|max:100',
            'categories.*.description'           => 'nullable|string',
            'categories.*.sop_checklist'         => 'required|array|min:1',
            'categories.*.sop_checklist.*'       => 'required|string|max:255',
        ], [
            'mata_praktik.required'     => 'Mata praktik wajib dipilih.',
            'tingkat_kelas.required'    => 'Tingkat kelas wajib dipilih.',
            'categories.*.name.required'=> 'Nama kriteria wajib diisi untuk setiap kategori.',
        ]);

        // Validasi total bobot = 100
        $totalBobot = collect($request->categories)->sum(fn($c) => (int)($c['weight'] ?? 0));
        if ($totalBobot !== 100) {
            return back()->withInput()
                ->with('error', "Total bobot harus 100%. Saat ini: {$totalBobot}%");
        }

        try {
            $isActive = $request->boolean('is_active', true);

            foreach ($request->categories as $key => $cat) {
                // key = nama kategori (persiapan, pelaksanaan, hasil, sikap)
                KriteriaPenilaian::create([
                    'name'          => $cat['name'],
                    'kategori'      => $key,
                    'weight'        => (int) $cat['weight'],
                    'description'   => $cat['description'] ?? null,
                    'sop_checklist' => array_values(array_filter($cat['sop_checklist'] ?? [])),
                    'mata_praktik'  => $request->mata_praktik,
                    'tingkat_kelas' => $request->tingkat_kelas,
                    'is_active'     => $isActive,
                ]);
            }

            return redirect()->route('admin.kriteria-penilaian.index')
                ->with('success', 'Semua kategori kriteria berhasil ditambahkan.');

        } catch (\Throwable $e) {
            Log::error('KriteriaPenilaianController::storeCombined: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(KriteriaPenilaian $kriteriaPenilaian): View
    {
        return view('admin.kriteria-penilaian.show', compact('kriteriaPenilaian'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(KriteriaPenilaian $kriteriaPenilaian): View
    {
        return view('admin.kriteria-penilaian.edit', [
            'kriteriaPenilaian' => $kriteriaPenilaian,
            'kategoriList'      => KriteriaPenilaian::getKategoriList(),
            'tingkatKelasList'  => KriteriaPenilaian::getTingkatKelasList(),
            'subjects'          => MataPelajaran::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, KriteriaPenilaian $kriteriaPenilaian): RedirectResponse
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'kategori'          => 'required|in:persiapan,pelaksanaan,hasil,sikap',
            'weight'            => 'required|integer|min:1|max:100',
            'description'       => 'nullable|string',
            'mata_praktik'      => 'required|string|max:255',
            'tingkat_kelas'     => 'required|in:X,XI,XII',
            'sop_checklist'     => 'required|array|min:1',
            'sop_checklist.*'   => 'required|string|max:255',
        ], [
            'name.required'          => 'Nama kriteria wajib diisi.',
            'kategori.required'      => 'Kategori wajib dipilih.',
            'weight.required'        => 'Bobot wajib diisi.',
            'mata_praktik.required'  => 'Mata praktik wajib dipilih.',
            'tingkat_kelas.required' => 'Tingkat kelas wajib dipilih.',
        ]);

        try {
            $kriteriaPenilaian->update([
                'name'          => $request->name,
                'kategori'      => $request->kategori,
                'weight'        => $request->weight,
                'description'   => $request->description,
                'mata_praktik'  => $request->mata_praktik,
                'tingkat_kelas' => $request->tingkat_kelas,
                'sop_checklist' => array_values(array_filter($request->sop_checklist)),
                'is_active'     => $request->boolean('is_active'),  // false when select = 0
            ]);

            return redirect()->route('admin.kriteria-penilaian.index')
                ->with('success', "Kriteria '{$kriteriaPenilaian->name}' berhasil diperbarui.");

        } catch (\Throwable $e) {
            Log::error('KriteriaPenilaianController::update: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui kriteria: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(KriteriaPenilaian $kriteriaPenilaian): RedirectResponse
    {
        // Cek relasi hanya jika tabel detail_penilaian ada
        if (Schema::hasTable('detail_penilaian')) {
            $jumlah = $kriteriaPenilaian->nilaiPraktik()->count();
            if ($jumlah > 0) {
                return back()->with('error',
                    "Tidak dapat menghapus '{$kriteriaPenilaian->name}' karena digunakan pada {$jumlah} data penilaian."
                );
            }
        }

        try {
            $nama = $kriteriaPenilaian->name;
            $kriteriaPenilaian->delete();

            return redirect()->route('admin.kriteria-penilaian.index')
                ->with('success', "Kriteria '{$nama}' berhasil dihapus.");

        } catch (\Throwable $e) {
            Log::error('KriteriaPenilaianController::destroy: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // ── Toggle status ─────────────────────────────────────────────────────

    public function toggleStatus(KriteriaPenilaian $kriteriaPenilaian): RedirectResponse
    {
        try {
            $kriteriaPenilaian->update(['is_active' => !$kriteriaPenilaian->is_active]);
            $status = $kriteriaPenilaian->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()->route('admin.kriteria-penilaian.index')
                ->with('success', "Kriteria '{$kriteriaPenilaian->name}' berhasil {$status}.");

        } catch (\Throwable $e) {
            Log::error('KriteriaPenilaianController::toggleStatus: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    // ── Seed default ──────────────────────────────────────────────────────

    public function seedDefault(): RedirectResponse
    {
        try {
            KriteriaPenilaian::seedDefault();

            return redirect()->route('admin.kriteria-penilaian.index')
                ->with('success', 'Default kriteria penilaian berhasil ditambahkan.');

        } catch (\Throwable $e) {
            Log::error('KriteriaPenilaianController::seedDefault: ' . $e->getMessage());
            return redirect()->route('admin.kriteria-penilaian.index')
                ->with('error', 'Gagal menambahkan default kriteria: ' . $e->getMessage());
        }
    }

    // ── AJAX: get kriteria by mata praktik + tingkat ──────────────────────

    public function getKriteria(Request $request)
    {
        $kriteria = KriteriaPenilaian::active()
            ->byMataPraktik($request->input('mata_praktik'))
            ->byTingkatKelas($request->input('tingkat_kelas'))
            ->orderBy('kategori')
            ->get();

        return response()->json($kriteria);
    }

    // ── Duplicate ─────────────────────────────────────────────────────────

    public function duplicate(Request $request, KriteriaPenilaian $kriteriaPenilaian): RedirectResponse
    {
        $validated = $request->validate([
            'target_mata_praktik'  => 'required|string|max:255',
            'target_tingkat_kelas' => 'required|in:X,XI,XII',
        ]);

        $exists = KriteriaPenilaian::where('name', $kriteriaPenilaian->name)
            ->where('mata_praktik', $validated['target_mata_praktik'])
            ->where('tingkat_kelas', $validated['target_tingkat_kelas'])
            ->exists();

        if ($exists) {
            return back()->with('error',
                "Kriteria '{$kriteriaPenilaian->name}' sudah ada di target mata praktik dan tingkat kelas tersebut."
            );
        }

        try {
            $new = $kriteriaPenilaian->replicate();
            $new->mata_praktik  = $validated['target_mata_praktik'];
            $new->tingkat_kelas = $validated['target_tingkat_kelas'];
            $new->save();

            return redirect()->route('admin.kriteria-penilaian.index')
                ->with('success', "Kriteria '{$kriteriaPenilaian->name}' berhasil diduplikasi.");

        } catch (\Throwable $e) {
            Log::error('KriteriaPenilaianController::duplicate: ' . $e->getMessage());
            return back()->with('error', 'Gagal menduplikasi: ' . $e->getMessage());
        }
    }

    // ── Export template ───────────────────────────────────────────────────

    public function exportTemplate()
    {
        $kriteria = KriteriaPenilaian::active()->get();
        $filename = 'template_kriteria_penilaian_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($kriteria->toArray())
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
