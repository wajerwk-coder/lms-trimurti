<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePenilaianRequest;
use App\Http\Requests\UpdatePenilaianRequest;
use App\Models\NilaiPraktik;
use App\Models\AssignmentSubmission;
use App\Models\KriteriaPenilaian;
use App\Models\Subject;
use App\Models\Kelas;
use App\Models\Assignment;
use App\Models\Practical;
use App\Models\Siswa;
use App\Traits\ResolvesAcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Include the trait
require_once base_path('app/Traits/PenilaianWithCriteriaTrait.php');

class PenilaianController extends Controller
{
    use PenilaianWithCriteriaTrait;
    use ResolvesAcademicPeriod;
    
    /**
     * ── Halaman Index Penilaian Praktik ──────────────────────────────────────
     * Menampilkan semua praktikum milik guru beserta status penilaian tiap siswa.
     */
    public function penilaianPraktik(Request $request): View
    {
        $guruId = Auth::id();

        $practicals = Practical::with(['subject', 'kelas'])
            ->where('guru_id', $guruId)
            ->when($request->filled('search'), fn($q) =>
                $q->where('title', 'like', '%' . $request->search . '%')
            )
            ->latest()
            ->get();

        // ── Preload: 1 query untuk semua NilaiPraktik (bukan N+1) ────────────
        $praktikalIds = $practicals->pluck('id');
        $allNilai     = NilaiPraktik::whereIn('practical_id', $praktikalIds)
            ->whereNull('criteria_id')
            ->whereNotNull('score')
            ->get()
            ->groupBy('practical_id');   // key: practical_id

        // ── Preload: 1 query untuk semua Siswa (bukan N+1 per kelas) ─────────
        $kelasIds   = $practicals->pluck('kelas_id')->filter()->unique();
        $allSiswa   = Siswa::with('user')
            ->whereNull('deleted_at')
            ->when($kelasIds->isNotEmpty(), fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->get()
            ->groupBy('kelas_id');   // key: kelas_id

        $practicals = $practicals->map(function ($p) use ($allNilai, $allSiswa) {
            $siswaList  = $p->kelas_id
                ? ($allSiswa->get($p->kelas_id) ?? collect())
                : $allSiswa->flatten(1);

            $nilaiList    = $allNilai->get($p->id) ?? collect();
            $totalSiswa   = $siswaList->count();
            $sudahDinilai = $nilaiList->count();

            $p->total_siswa   = $totalSiswa;
            $p->sudah_dinilai = $sudahDinilai;
            $p->belum_dinilai = max(0, $totalSiswa - $sudahDinilai);
            $p->rata_rata     = $nilaiList->isNotEmpty()
                ? round($nilaiList->avg('score'), 1)
                : null;
            $p->siswa_list    = $siswaList;
            // Map nilai keyed by siswa_id untuk lookup O(1) di view
            $p->nilai_map     = $nilaiList->keyBy('siswa_id');

            return $p;
        });

        $stats = [
            'total_praktikum'  => $practicals->count(),
            'total_dinilai'    => $practicals->sum('sudah_dinilai'),
            'total_belum'      => $practicals->sum('belum_dinilai'),
            'rata_rata_global' => $practicals->whereNotNull('rata_rata')->avg('rata_rata')
                ? round($practicals->whereNotNull('rata_rata')->avg('rata_rata'), 1)
                : 0,
        ];

        return view('guru.penilaian.praktik-index', compact('practicals', 'stats'));
    }

    /**
     * Display a listing of the assessments.
     */
    public function index(): View
    {
        $guruId = Auth::id();

        // ── TAB 1: Penilaian Tugas (assignment submissions) ───────────────────
        $assignmentSubmissions = AssignmentSubmission::whereHas('assignment', fn($q) =>
                $q->where('guru_id', $guruId)
            )
            ->with(['assignment.subject', 'siswa'])
            ->latest()
            ->get();

        // ── TAB 2: Penilaian Praktikum (per siswa, summary scores) ───────────
        $rawPracticals = Practical::with(['subject', 'kelas'])
            ->where('guru_id', $guruId)
            ->latest()
            ->get();

        // Preload semua NilaiPraktik & Siswa dengan bulk query (bukan N+1)
        $pracIds2      = $rawPracticals->pluck('id');
        $allNilai2     = NilaiPraktik::whereIn('practical_id', $pracIds2)
            ->whereNull('criteria_id')->whereNotNull('score')
            ->get()->groupBy('practical_id');

        $kelasIds2     = $rawPracticals->pluck('kelas_id')->filter()->unique();
        $allSiswa2     = Siswa::with(['user', 'kelas'])->whereNull('deleted_at')
            ->when($kelasIds2->isNotEmpty(), fn($q) => $q->whereIn('kelas_id', $kelasIds2))
            ->get()->groupBy('kelas_id');

        $practicals = $rawPracticals->map(function ($p) use ($allNilai2, $allSiswa2) {
            $siswaList    = $p->kelas_id
                ? ($allSiswa2->get($p->kelas_id) ?? collect())
                : $allSiswa2->flatten(1);
            $nilaiList    = $allNilai2->get($p->id) ?? collect();

            $p->siswa_list    = $siswaList;
            $p->sudah_dinilai = $nilaiList->count();
            $p->belum_dinilai = max(0, $siswaList->count() - $nilaiList->count());
            $p->total_siswa   = $siswaList->count();
            $p->rata_rata     = $nilaiList->isNotEmpty() ? round($nilaiList->avg('score'), 1) : null;
            $p->nilai_map     = $nilaiList->keyBy('siswa_id');
            return $p;
        });

        // ── Stats ─────────────────────────────────────────────────────────────
        $stats = [
            // Tugas
            'total_tugas'         => $assignmentSubmissions->count(),
            'tugas_belum_dinilai' => $assignmentSubmissions->whereNull('score')->count(),
            'tugas_sudah_dinilai' => $assignmentSubmissions->whereNotNull('score')->count(),
            'rata_rata_tugas'     => round($assignmentSubmissions->whereNotNull('score')->avg('score') ?? 0, 1),
            // Praktikum
            'total_praktikum'     => $practicals->count(),
            'praktik_dinilai'     => $practicals->sum('sudah_dinilai'),
            'praktik_belum'       => $practicals->sum('belum_dinilai'),
            'rata_rata_praktik'   => $practicals->whereNotNull('rata_rata')->avg('rata_rata')
                ? round($practicals->whereNotNull('rata_rata')->avg('rata_rata'), 1)
                : 0,
        ];

        // Default active tab dari query string
        $activeTab = request('tab', 'tugas');

        return view('guru.penilaian.index', compact(
            'assignmentSubmissions',
            'practicals',
            'stats',
            'activeTab'
        ));
    }
    /**
     * Show the form for creating a new assessment.
     */
    public function create(): View
    {
        $guruId = Auth::id();

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $classes  = Kelas::orderBy('name')->get();

        $assignments = Assignment::where('guru_id', $guruId)
            ->with('subject')->latest()->get();

        // Fallback: tampilkan semua tugas jika guru belum punya tugas sendiri
        if ($assignments->isEmpty()) {
            $assignments = Assignment::where('is_published', true)
                ->with('subject')->latest()->get();
        }

        $practicals = Practical::where('guru_id', $guruId)
            ->with('subject')->latest()->get();

        // Fallback: tampilkan semua praktikum jika guru belum punya praktikum sendiri
        if ($practicals->isEmpty()) {
            $practicals = Practical::where('is_published', true)
                ->with('subject')->latest()->get();
        }

        // Siswa tidak punya kolom 'name' — load via user relation
        $students = Siswa::with('user')
            ->whereNull('deleted_at')
            ->get()
            ->sortBy(fn($s) => $s->user?->name);

        return view('guru.penilaian.create', compact('subjects', 'classes', 'assignments', 'practicals', 'students'));
    }

    /**
     * Store a newly created assessment.
     */
    public function store(Request $request): RedirectResponse
    {
        $guruId = Auth::id();

        // Form sends 'assessment_type', validate it as 'type' internally
        $request->validate([
            'assessment_type' => 'required|in:assignment,practical',
            'assignment_id'   => 'required_if:assessment_type,assignment|exists:assignments,id',
            'practical_id'    => 'required_if:assessment_type,practical|exists:practicals,id',
            'siswa_id'        => 'required|exists:siswa,id',
            'score'           => 'required|numeric|min:0|max:1000',
            'feedback'        => 'nullable|string|max:1000',
        ], [
            'assessment_type.required' => 'Tipe penilaian wajib dipilih.',
            'assignment_id.required_if'=> 'Tugas wajib dipilih.',
            'practical_id.required_if' => 'Praktikum wajib dipilih.',
            'siswa_id.required'        => 'Siswa wajib dipilih.',
            'score.required'           => 'Nilai wajib diisi.',
        ]);

        // Konversi siswa.id → users_central.id
        // Form bisa kirim 'siswa_id' (tugas) atau 'siswa_id_practical' (praktikum)
        $siswaIdRaw = $request->assessment_type === 'practical'
            ? ($request->siswa_id_practical ?? $request->siswa_id)
            : $request->siswa_id;

        $siswa = Siswa::findOrFail($siswaIdRaw);
        $ucId  = $siswa->user_id;

        try {
            if ($request->assessment_type === 'assignment') {
                // Coba cari milik guru sendiri, fallback ke siapapun
                $assignment = Assignment::where('id', $request->assignment_id)
                    ->firstOrFail();

                // Ownership: guru sendiri atau fallback (assignment milik guru lain tapi guru ini menilai)
                AssignmentSubmission::updateOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'siswa_id'      => $ucId,
                    ],
                    [
                        'student_id'   => $ucId,
                        'score'        => $request->score,
                        'feedback'     => $request->feedback,
                        'status'       => 'graded',
                        'graded_by'    => $guruId,
                        'graded_at'    => now(),
                        'submitted_at' => now(),
                    ]
                );
                $message = 'Penilaian tugas berhasil disimpan.';

            } else {
                // Coba cari milik guru sendiri, fallback ke siapapun
                $practical = Practical::where('id', $request->practical_id)
                    ->firstOrFail();

                NilaiPraktik::updateOrCreate(
                    [
                        'practical_id' => $practical->id,
                        'siswa_id'     => $ucId,
                    ],
                    [
                        'academic_period_id' => $this->resolvePeriodId($practical->kelas_id),
                        'guru_id'    => $guruId,
                        'graded_by'  => $guruId,
                        'score'      => $request->score,
                        'feedback'   => $request->feedback,
                        'graded_at'  => now(),
                    ]
                );
                $message = 'Penilaian praktikum berhasil disimpan. Nilai: ' . number_format($request->score, 1);
            }

            return redirect()->route('guru.penilaian.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to save assessment: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan penilaian: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified assessment.
     */
    public function edit($id): View
    {
        // Cari berdasarkan tipe yang dikirim via query string untuk hindari ID clash
        // antara AssignmentSubmission dan NilaiPraktik yang sama-sama auto-increment
        $type = request('type', 'assignment');

        if ($type === 'practical') {
            $submission = NilaiPraktik::findOrFail($id);
        } else {
            $submission = AssignmentSubmission::findOrFail($id);
        }

        // Ownership check
        $guruId = Auth::id();
        if ($submission instanceof AssignmentSubmission) {
            if ($submission->assignment?->guru_id !== $guruId) abort(403);
        } elseif ($submission instanceof NilaiPraktik) {
            if ($submission->guru_id !== $guruId) abort(403);
        }

        $subjects   = Subject::where('is_active', true)->orderBy('name')->get();
        $classes    = Kelas::orderBy('name')->get();
        $students   = Siswa::with('user')->whereNull('deleted_at')->get()
                        ->sortBy(fn($s) => $s->user?->name);
        $assignments = Assignment::where('guru_id', $guruId)->with('subject')->latest()->get();
        $practicals  = Practical::where('guru_id', $guruId)->with('subject')->latest()->get();

        return view('guru.penilaian.edit', compact(
            'submission', 'subjects', 'classes', 'students', 'assignments', 'practicals'
        ));
    }

    /**
     * Update the specified assessment in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $guruId = Auth::id();

        $type = $request->input('submission_type', request('type', 'assignment'));
        if ($type === 'practical') {
            $submission = NilaiPraktik::findOrFail($id);
        } else {
            $submission = AssignmentSubmission::findOrFail($id);
        }

        $request->validate([
            'score'    => 'required|numeric|min:0|max:1000',
            'feedback' => 'nullable|string|max:1000',
        ]);

        try {
            if ($submission instanceof AssignmentSubmission) {
                if ($submission->assignment?->guru_id !== $guruId) abort(403);
                $submission->update([
                    'score'     => $request->score,
                    'feedback'  => $request->feedback,
                    'status'    => 'graded',
                    'graded_by' => $guruId,
                    'graded_at' => now(),
                ]);
            } elseif ($submission instanceof NilaiPraktik) {
                if ($submission->guru_id !== $guruId) abort(403);
                $submission->update([
                    'score'     => $request->score,
                    'feedback'  => $request->feedback,
                    'graded_at' => now(),
                    'graded_by' => $guruId,
                ]);
            }

            return redirect()->route('guru.penilaian.index')
                ->with('success', 'Penilaian berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Failed to update assessment: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui penilaian: ' . $e->getMessage());
        }
    }

    /**
     * ── SISTEM BARU: Penilaian berbasis Kriteria Admin ────────────────────────
     * Guru pilih praktikum → kelas otomatis dari praktikum →
     * semua siswa di kelas muncul → nilai per siswa dengan SOP checklist.
     */
    public function nilaiKriteria(): View
    {
        $guruId = Auth::id();

        // Semua praktikum milik guru (dengan relasi kelas untuk ambil siswa)
        $practicals = Practical::where('guru_id', $guruId)
            ->with(['subject', 'kelas.siswa.user'])
            ->latest()
            ->get();

        if ($practicals->isEmpty()) {
            $practicals = Practical::with(['subject', 'kelas.siswa.user'])
                ->latest()
                ->get();
        }

        // Pre-select dari query string
        $selectedPractical = request('practical_id');

        $practical     = null;
        $siswaList     = collect();
        $kriteriaByCat = collect();
        $existingNilai = collect(); // key: siswa_id (users_central.id)

        if ($selectedPractical) {
            $practical = Practical::with(['subject', 'kelas.siswa.user'])
                ->find($selectedPractical);

            if ($practical) {
                // Ambil semua siswa di kelas praktikum ini
                $siswaList = $practical->kelas
                    ? Siswa::with('user')
                        ->where('kelas_id', $practical->kelas_id)
                        ->whereNull('deleted_at')
                        ->orderBy('id')
                        ->get()
                    : collect();

                // Ambil kriteria berdasarkan mata pelajaran praktikum
                $mataPraktik   = $practical->subject?->name ?? '';
                $kriteriaByCat = KriteriaPenilaian::active()
                    ->where('mata_praktik', $mataPraktik)
                    ->orderBy('kategori')
                    ->orderBy('name')
                    ->get()
                    ->groupBy('kategori');

                // Preload nilai yang sudah ada untuk semua siswa di praktikum ini
                // key: "{practical_id}_{siswa_id(uc)}_{criteria_id|null}"
                $ucIds = $siswaList->pluck('user_id');
                $existingNilai = NilaiPraktik::where('practical_id', $selectedPractical)
                    ->whereIn('siswa_id', $ucIds)
                    ->get()
                    ->groupBy(fn($n) => $n->siswa_id . '_' . ($n->criteria_id ?? 'null'));
            }
        }

        return view('guru.penilaian.nilai-kriteria', compact(
            'practicals',
            'selectedPractical',
            'practical',
            'siswaList',
            'kriteriaByCat',
            'existingNilai',
        ));
    }

    /**
     * Store penilaian batch berbasis kriteria — satu record per kriteria per siswa.
     * Menerima array siswa_ids + checklist per kriteria per siswa.
     */
    public function storeNilaiKriteria(Request $request): RedirectResponse
    {
        $request->validate([
            'practical_id'          => 'required|exists:practicals,id',
            'siswa_ids'             => 'required|array|min:1',
            'siswa_ids.*'           => 'exists:siswa,id',
            'kriteria'              => 'required|array|min:1',
            'kriteria.*.id'         => 'required|exists:assessment_criteria,id',
        ], [
            'practical_id.required' => 'Praktikum wajib dipilih.',
            'siswa_ids.required'    => 'Minimal satu siswa harus dinilai.',
            'kriteria.required'     => 'Minimal satu kriteria wajib ada.',
        ]);

        $guruId    = Auth::id();
        $practical = Practical::findOrFail($request->practical_id);

        // Resolve periode aktif sekali saja
        if (method_exists($this, 'resolvePeriodId')) {
            $periodId = $this->resolvePeriodId($practical->kelas_id);
        } else {
            $kelas    = $practical->kelas_id ? Kelas::find($practical->kelas_id) : null;
            $periodId = $kelas?->academic_period_id
                ?? \App\Models\AcademicPeriod::getActive()?->id;
        }

        // Kumpulkan semua KriteriaPenilaian sekaligus (1 query)
        $kriteriaIds  = collect($request->kriteria)->pluck('id');
        $kriteriaMap  = KriteriaPenilaian::whereIn('id', $kriteriaIds)
            ->get()
            ->keyBy('id');

        DB::beginTransaction();
        try {
            $savedCount = 0;

            foreach ($request->siswa_ids as $siswaId) {
                $siswa = Siswa::find($siswaId);
                if (!$siswa) continue;
                $ucId = $siswa->user_id;

                // Ambil feedback per siswa
                $feedbackSiswa = $request->input("feedback.{$siswaId}", '');

                $nilaiAkhir  = 0;
                $totalBobot  = 0;
                $kriteriaResults = [];

                // Pass 1: hitung nilai per kriteria
                foreach ($request->kriteria as $ki => $kriteriaData) {
                    $kriteria   = $kriteriaMap->get($kriteriaData['id']);
                    if (!$kriteria) continue;

                    $sopList    = is_array($kriteria->sop_checklist) ? $kriteria->sop_checklist : [];
                    $totalSop   = count($sopList);

                    // Checklist diambil per siswa: kriteria[ki][checklist][siswa_id][]
                    $checkedSop = $request->input("kriteria.{$ki}.checklist.{$siswaId}", []);
                    $checkedSop = is_array($checkedSop) ? $checkedSop : [];

                    $nilaiKriteria = $totalSop > 0
                        ? round((count($checkedSop) / $totalSop) * 100, 2)
                        : 100;

                    $totalBobot    += $kriteria->weight;
                    $kriteriaResults[] = compact('kriteria', 'sopList', 'totalSop', 'checkedSop', 'nilaiKriteria', 'ki');
                }

                // Pass 2: nilai akhir ternormalisasi
                $bobotDivisor = $totalBobot > 0 ? $totalBobot : 100;
                foreach ($kriteriaResults as $item) {
                    $nilaiAkhir += ($item['nilaiKriteria'] * $item['kriteria']->weight / $bobotDivisor);

                    // Simpan per kriteria per siswa
                    NilaiPraktik::updateOrCreate(
                        [
                            'practical_id' => $practical->id,
                            'siswa_id'     => $ucId,
                            'criteria_id'  => $item['kriteria']->id,
                        ],
                        [
                            'academic_period_id' => $periodId,
                            'guru_id'   => $guruId,
                            'graded_by' => $guruId,
                            'score'     => $item['nilaiKriteria'],
                            'feedback'  => json_encode([
                                'checked_sop'   => $item['checkedSop'],
                                'total_sop'     => $item['totalSop'],
                                'kriteria_name' => $item['kriteria']->name,
                                'general_note'  => $feedbackSiswa,
                            ]),
                            'graded_at' => now(),
                        ]
                    );
                }

                $nilaiAkhir = min(100, round($nilaiAkhir, 2));

                // Simpan nilai akhir (summary, criteria_id = null)
                NilaiPraktik::updateOrCreate(
                    [
                        'practical_id' => $practical->id,
                        'siswa_id'     => $ucId,
                        'criteria_id'  => null,
                    ],
                    [
                        'academic_period_id' => $periodId,
                        'guru_id'   => $guruId,
                        'graded_by' => $guruId,
                        'score'     => $nilaiAkhir,
                        'feedback'  => $feedbackSiswa,
                        'graded_at' => now(),
                    ]
                );

                $savedCount++;
            }

            DB::commit();

            Log::info('Penilaian kriteria batch saved', [
                'practical_id' => $practical->id,
                'guru_id'      => $guruId,
                'siswa_count'  => $savedCount,
            ]);

            return redirect()
                ->route('guru.penilaian.index')
                ->with('success', "Penilaian berhasil disimpan untuk {$savedCount} siswa.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeNilaiKriteria batch failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan penilaian: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: ambil kriteria berdasarkan practical_id
     */
    public function getKriteriaByPractical(Request $request)
    {
        $practical = Practical::with('subject')->find($request->practical_id);
        if (!$practical) {
            return response()->json(['kriteria' => [], 'mata_praktik' => '']);
        }

        $mataPraktik = $practical->subject?->name ?? '';
        $kriteria = KriteriaPenilaian::active()
            ->where('mata_praktik', $mataPraktik)
            ->orderBy('kategori')
            ->get()
            ->map(fn($k) => [
                'id'           => $k->id,
                'name'         => $k->name,
                'kategori'     => $k->kategori,
                'kategori_label'=> $k->kategori_label,
                'weight'       => $k->weight,
                'description'  => $k->description,
                'sop_checklist'=> $k->sop_checklist ?? [],
            ]);

        return response()->json([
            'kriteria'    => $kriteria,
            'mata_praktik'=> $mataPraktik,
            'has_kriteria'=> $kriteria->isNotEmpty(),
        ]);
    }

    /**
     * ── End sistem baru ───────────────────────────────────────────────────────
     */

    /**
     * Remove the specified assessment from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $guruId = Auth::id();
        
        // Find assessment
        $assessment = AssignmentSubmission::find($id) ?? NilaiPraktik::find($id);
        
        if (!$assessment) {
            abort(404);
        }
        
        // Verify ownership
        if ($assessment instanceof AssignmentSubmission) {
            if ($assessment->assignment->guru_id !== $guruId) {
                abort(403);
            }
        } elseif ($assessment instanceof NilaiPraktik) {
            if ($assessment->guru_id !== $guruId) {
                abort(403);
            }
        }
        
        $assessment->delete();
        
        return redirect()
            ->route('guru.penilaian.index')
            ->with('success', 'Penilaian berhasil dihapus!');
    }

    /**
     * Show auto assessment page.
     */
    public function autoAssessment(): View
    {
        $guruId = Auth::id();
        
        // Get data
        $subjects = Subject::where('is_active', true)->with('jurusan')->get();
        $classes = Kelas::with('jurusan')->get();
        
        // Get students with proper class relationships
        $students = Siswa::with(['user', 'kelas'])
            ->whereNull('deleted_at')
            ->get()
            ->sortBy(fn($s) => $s->user?->name);
        
        $assignments = Assignment::where('guru_id', $guruId)
            ->with(['subject.jurusan'])
            ->latest()
            ->get();
            
        $practicals = Practical::where('guru_id', $guruId)
            ->with(['subject.jurusan', 'kelas.jurusan'])
            ->latest()
            ->get();
            
        // If no practicals found, try to get all practicals for testing
        if ($practicals->count() === 0) {
            $practicals = Practical::with(['subject.jurusan', 'kelas.jurusan'])
                ->latest()
                ->get();
        }
        
        return view('guru.penilaian.auto', compact('subjects', 'classes', 'students', 'assignments', 'practicals'));
    }

    /**
     * Show auto assessment with criteria page.
     */
    public function autoWithCriteria(): View
    {
        $guruId = Auth::id();
        
        // Get data
        $subjects = Subject::where('is_active', true)->with('jurusan')->get();
        $classes = Kelas::with('jurusan')->get();
        
        // Get students with proper class relationships
        $students = Siswa::with(['user', 'kelas'])
            ->whereNull('deleted_at')
            ->get()
            ->sortBy(fn($s) => $s->user?->name);
        
        $assignments = Assignment::where('guru_id', $guruId)
            ->with(['subject.jurusan'])
            ->latest()
            ->get();
            
        $practicals = Practical::where('guru_id', $guruId)
            ->with(['subject.jurusan', 'kelas.jurusan'])
            ->latest()
            ->get();
            
        // If no practicals found, try to get all practicals for testing
        if ($practicals->count() === 0) {
            $practicals = Practical::with(['subject.jurusan', 'kelas.jurusan'])
                ->latest()
                ->get();
        }
        
        return view('guru.penilaian.auto_with_criteria', compact('subjects', 'classes', 'students', 'assignments', 'practicals'));
    }

    /**
     * Save auto assessment.
     */
    public function saveAutoAssessment(Request $request): RedirectResponse
    {
        $guruId = Auth::id();

        $request->validate([
            'siswa_id'        => 'required|exists:siswa,id',
            'practical_id'    => 'required|exists:practicals,id',
            'kriteria_nilai'  => 'required|array',
            'feedback'        => 'required|string|max:2000',
            'assessment_date' => 'required|date|before_or_equal:today',
        ]);

        try {
            // Verify guru owns practical
            $practical = Practical::where('id', $request->practical_id)
                ->where('guru_id', $guruId)
                ->firstOrFail();

            // Get criteria weights
            $criteriaWeights = [
                'prep_1' => 0.20, 'prep_2' => 0.15, 'prep_3' => 0.15,
                'exec_1' => 0.25, 'exec_2' => 0.20, 'exec_3' => 0.20,
                'result_1' => 0.30, 'result_2' => 0.20,
                'att_1' => 0.15, 'att_2' => 0.20
            ];

            $totalWeightedScore = 0;
            $checkedCriteria = $request->kriteria_nilai ?? [];

            foreach ($criteriaWeights as $criterionId => $weight) {
                if (in_array($criterionId, $checkedCriteria)) {
                    $totalWeightedScore += 100 * $weight;
                }
            }

            // Create assessment record using correct NilaiPraktik columns
            $nilai = NilaiPraktik::updateOrCreate(
                [
                    'practical_id' => $practical->id,
                    'siswa_id'     => $request->siswa_id,
                ],
                [
                    'guru_id'   => $guruId,
                    'graded_by' => $guruId,
                    'score'     => $totalWeightedScore,
                    'feedback'  => $request->feedback,
                    'graded_at' => $request->assessment_date ?? now(),
                ]
            );

            return redirect()
                ->route('guru.penilaian.index')
                ->with('success', 'Penilaian otomatis berhasil disimpan! Nilai: ' . number_format($totalWeightedScore, 1));

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal menyimpan penilaian: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Save auto assessment with criteria.
     */
    public function saveAutoAssessmentWithCriteria(Request $request): RedirectResponse
    {
        $guruId = Auth::id();

        $request->validate([
            'siswa_id'        => 'required|exists:siswa,id',
            'practical_id'    => 'required|exists:practicals,id',
            'kriteria_nilai'  => 'required|array',
            'feedback'        => 'required|string|max:2000',
            'assessment_date' => 'required|date|before_or_equal:today',
        ]);

        try {
            // Verify guru owns practical
            $practical = Practical::where('id', $request->practical_id)
                ->where('guru_id', $guruId)
                ->firstOrFail();

            // Get SOP criteria weights
            $criteriaWeights = [
                'prep_1' => 0.10, 'prep_2' => 0.10, 'prep_3' => 0.15,
                'exec_1' => 0.20, 'exec_2' => 0.15, 'exec_3' => 0.10,
                'eval_1' => 0.15, 'eval_2' => 0.05
            ];

            $totalWeightedScore = 0;
            $checkedCriteria = $request->kriteria_nilai ?? [];

            foreach ($criteriaWeights as $criterionId => $weight) {
                if (in_array($criterionId, $checkedCriteria)) {
                    $totalWeightedScore += 100 * $weight;
                }
            }

            // Create assessment record using correct NilaiPraktik columns
            $nilai = NilaiPraktik::updateOrCreate(
                [
                    'practical_id' => $practical->id,
                    'siswa_id'     => $request->siswa_id,
                ],
                [
                    'guru_id'   => $guruId,
                    'graded_by' => $guruId,
                    'score'     => $totalWeightedScore,
                    'feedback'  => $request->feedback,
                    'graded_at' => $request->assessment_date ?? now(),
                ]
            );

            return redirect()
                ->route('guru.penilaian.index')
                ->with('success', 'Penilaian SOP berhasil disimpan! Nilai: ' . number_format($totalWeightedScore, 1));

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal menyimpan penilaian: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Calculate grade based on score
     */
    public function calculateGrade($score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }

    /**
     * Export penilaian data
     */
    public function export(Request $request)
    {
        $guruId = Auth::id();
        $format = $request->get('format', 'excel');
        
        try {
            // Get all assessments for this guru
            $assignmentSubmissions = AssignmentSubmission::whereHas('assignment', function($query) use ($guruId) {
                    $query->where('guru_id', $guruId);
                })
                ->with(['assignment.subject', 'siswa.kelas'])
                ->latest()
                ->get();
                
            $nilaiPraktiks = NilaiPraktik::with(['siswa', 'guru', 'practical.subject'])
                ->where('graded_by', $guruId)
                ->latest('graded_at')
                ->get();
            
            // Combine and sort all assessments
            $allAssessments = collect()
                ->merge($assignmentSubmissions)
                ->merge($nilaiPraktiks)
                ->sortByDesc(function($assessment) {
                    return $assessment->updated_at ?? $assessment->graded_at ?? $assessment->created_at;
                });
            
            // Prepare data for export
            $exportData = $allAssessments->map(function($assessment) {
                $score = $this->getAssessmentScore($assessment);
                
                return [
                    'ID' => $assessment->id,
                    'Tipe' => $assessment->assignment_id ? 'Tugas' : 'Praktikum',
                    'NIS' => $assessment->siswa->nis_nip ?? '-',
                    'Nama Siswa' => $assessment->siswa->name ?? '-',
                    'Email Siswa' => $assessment->siswa->email ?? '-',
                    'Kelas' => $assessment->siswa->kelas->name ?? '-',
                    'Mata Pelajaran' => $assessment->assignment_id 
                        ? ($assessment->assignment->subject->name ?? '-')
                        : ($assessment->practical->subject->name ?? '-'),
                    'Judul' => $assessment->assignment_id 
                        ? $assessment->assignment->title
                        : $assessment->practical->title,
                    'Nilai' => $score ?? '-',
                    'Grade' => $score ? $this->calculateGrade($score) : '-',
                    'Status' => $score ? 'Sudah Dinilai' : 'Belum Dinilai',
                    'Tanggal' => $assessment->assignment_id 
                        ? ($assessment->assignment->due_date ? $assessment->assignment->due_date->format('d M Y H:i') : '-')
                        : ($assessment->practical->date ? $assessment->practical->date->format('d M Y') : '-'),
                    'Feedback' => $assessment->feedback ?? '-',
                ];
            });
            
            if ($format === 'excel') {
                return $this->exportExcel($exportData);
            } elseif ($format === 'pdf') {
                return $this->exportPdf($exportData);
            } else {
                return back()->with('error', 'Format tidak didukung');
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }
    
    /**
     * Export to Excel
     */
    private function exportExcel($data)
    {
        $filename = 'penilaian_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, array_keys($data->first()));
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Export to PDF
     */
    private function exportPdf($data)
    {
        $filename = 'penilaian_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = \PDF::loadView('guru.penilaian.export-pdf', compact('data'));
        
        return $pdf->download($filename);
    }
    
    /**
     * Helper function to get assessment score
     */
    public function getAssessmentScore($assessment)
    {
        if (isset($assessment->score) && $assessment->score !== null) {
            return (float) $assessment->score;
        }
        if (isset($assessment->total_nilai) && $assessment->total_nilai !== null) {
            return (float) $assessment->total_nilai;
        }
        return null;
    }
}
