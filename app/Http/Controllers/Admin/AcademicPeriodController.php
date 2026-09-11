<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AcademicPeriodController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = AcademicPeriod::withCount('kelas');

        // Filter tahun ajaran
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        // Filter semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // Filter status aktif
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $periods = $query->orderByDesc('academic_year')
                         ->orderByRaw("FIELD(semester,'ganjil','genap')")
                         ->paginate(15)
                         ->withQueryString();

        $activePeriod   = AcademicPeriod::getActive();
        $academicYears  = AcademicPeriod::select('academic_year')
                            ->distinct()
                            ->orderByDesc('academic_year')
                            ->pluck('academic_year');

        return view('admin.academic-periods.index', compact(
            'periods',
            'activePeriod',
            'academicYears',
        ));
    }

    // ── Create & Store ────────────────────────────────────────────────────────

    public function create(): View
    {
        // Suggestion: tahun ajaran berjalan
        $currentYear = date('Y');
        $suggestedYear = $currentYear . '/' . ($currentYear + 1);

        return view('admin.academic-periods.create', compact('suggestedYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year' => 'required|string|max:20|regex:/^\d{4}\/\d{4}$/',
            'semester'      => 'required|in:ganjil,genap',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'is_active'     => 'nullable|boolean',
            'description'   => 'nullable|string|max:500',
        ], [
            'academic_year.required' => 'Tahun ajaran wajib diisi.',
            'academic_year.regex'    => 'Format tahun ajaran harus YYYY/YYYY, contoh: 2025/2026.',
            'semester.required'      => 'Semester wajib dipilih.',
            'semester.in'            => 'Semester harus Ganjil atau Genap.',
            'end_date.after_or_equal'=> 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        // Cek duplikat
        $exists = AcademicPeriod::where('academic_year', $validated['academic_year'])
                    ->where('semester', $validated['semester'])
                    ->exists();
        if ($exists) {
            return back()->withInput()
                ->withErrors(['semester' => 'Periode ' . $validated['academic_year'] . ' semester ' . $validated['semester'] . ' sudah ada.']);
        }

        try {
            $isActive = (bool) ($validated['is_active'] ?? false);

            // Jika diset aktif, nonaktifkan semua periode lain dulu
            if ($isActive) {
                AcademicPeriod::query()->update(['is_active' => false]);
            }

            // Auto-generate name jika tidak diisi
            $name = $request->filled('name')
                ? $request->name
                : AcademicPeriod::generateName($validated['academic_year'], $validated['semester']);

            AcademicPeriod::create([
                'name'          => $name,
                'academic_year' => $validated['academic_year'],
                'semester'      => $validated['semester'],
                'start_date'    => $validated['start_date'] ?? null,
                'end_date'      => $validated['end_date']   ?? null,
                'is_active'     => $isActive,
                'description'   => $validated['description'] ?? null,
            ]);

            return redirect()->route('admin.academic-periods.index')
                ->with('success', 'Periode pembelajaran berhasil ditambahkan.');

        } catch (\Throwable $e) {
            Log::error('AcademicPeriodController::store: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan periode: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(AcademicPeriod $academicPeriod): View
    {
        $academicPeriod->loadCount('kelas');
        $kelasList = Kelas::with('jurusan')
                        ->where('academic_period_id', $academicPeriod->id)
                        ->withCount('siswa')
                        ->orderBy('grade')
                        ->orderBy('name')
                        ->get();

        return view('admin.academic-periods.show', compact('academicPeriod', 'kelasList'));
    }

    // ── Edit & Update ─────────────────────────────────────────────────────────

    public function edit(AcademicPeriod $academicPeriod): View
    {
        return view('admin.academic-periods.edit', compact('academicPeriod'));
    }

    public function update(Request $request, AcademicPeriod $academicPeriod): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year' => 'required|string|max:20|regex:/^\d{4}\/\d{4}$/',
            'semester'      => 'required|in:ganjil,genap',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'is_active'     => 'nullable|boolean',
            'description'   => 'nullable|string|max:500',
        ], [
            'academic_year.required' => 'Tahun ajaran wajib diisi.',
            'academic_year.regex'    => 'Format tahun ajaran harus YYYY/YYYY, contoh: 2025/2026.',
            'semester.required'      => 'Semester wajib dipilih.',
            'semester.in'            => 'Semester harus Ganjil atau Genap.',
            'end_date.after_or_equal'=> 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        // Cek duplikat (kecuali dirinya sendiri)
        $exists = AcademicPeriod::where('academic_year', $validated['academic_year'])
                    ->where('semester', $validated['semester'])
                    ->where('id', '!=', $academicPeriod->id)
                    ->exists();
        if ($exists) {
            return back()->withInput()
                ->withErrors(['semester' => 'Periode ' . $validated['academic_year'] . ' semester ' . $validated['semester'] . ' sudah ada.']);
        }

        try {
            $isActive = (bool) ($validated['is_active'] ?? false);

            if ($isActive && !$academicPeriod->is_active) {
                AcademicPeriod::query()->where('id', '!=', $academicPeriod->id)->update(['is_active' => false]);
            }

            $name = $request->filled('name')
                ? $request->name
                : AcademicPeriod::generateName($validated['academic_year'], $validated['semester']);

            $academicPeriod->update([
                'name'          => $name,
                'academic_year' => $validated['academic_year'],
                'semester'      => $validated['semester'],
                'start_date'    => $validated['start_date'] ?? null,
                'end_date'      => $validated['end_date']   ?? null,
                'is_active'     => $isActive,
                'description'   => $validated['description'] ?? null,
            ]);

            return redirect()->route('admin.academic-periods.index')
                ->with('success', 'Periode pembelajaran berhasil diperbarui.');

        } catch (\Throwable $e) {
            Log::error('AcademicPeriodController::update: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui periode: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(AcademicPeriod $academicPeriod): RedirectResponse
    {
        try {
            // Lepaskan FK di kelas yang terhubung
            Kelas::where('academic_period_id', $academicPeriod->id)
                 ->update(['academic_period_id' => null]);

            $academicPeriod->delete();

            return redirect()->route('admin.academic-periods.index')
                ->with('success', 'Periode pembelajaran berhasil dihapus.');

        } catch (\Throwable $e) {
            Log::error('AcademicPeriodController::destroy: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus periode: ' . $e->getMessage());
        }
    }

    // ── Set Active ────────────────────────────────────────────────────────────

    /**
     * Set periode ini sebagai periode aktif (satu-satunya yang aktif).
     */
    public function setActive(AcademicPeriod $academicPeriod): RedirectResponse
    {
        try {
            $academicPeriod->setAsActive();

            return redirect()->route('admin.academic-periods.index')
                ->with('success', '"' . $academicPeriod->full_label . '" ditetapkan sebagai periode aktif.');

        } catch (\Throwable $e) {
            Log::error('AcademicPeriodController::setActive: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengaktifkan periode: ' . $e->getMessage());
        }
    }
}
