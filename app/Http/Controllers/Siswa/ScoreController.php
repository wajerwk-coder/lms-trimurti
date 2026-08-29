<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\NilaiPraktik;
use App\Models\AssignmentSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ScoreController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function getSiswaContext(): array
    {
        $user    = Auth::user();
        $siswaId = $user->id;
        // kelas_id ada di tabel siswa, bukan users_central
        $kelasId = $user->siswa?->kelas_id;
        return [$siswaId, $kelasId];
    }

    // ── Public methods ─────────────────────────────────────────────────────────

    public function index(): View
    {
        [$siswaId, $kelasId] = $this->getSiswaContext();

        $practicalScores = NilaiPraktik::with(['practical.subject'])
            ->where('siswa_id', $siswaId)
            ->whereNull('criteria_id')   // hanya nilai summary, bukan per-kriteria
            ->when($kelasId, fn($q) => $q->whereHas('practical', fn($s) => $s->where('kelas_id', $kelasId)))
            ->latest()
            ->paginate(10, ['*'], 'practical_page');

        // AssignmentSubmission FK: student_id
        $assignmentScores = AssignmentSubmission::with(['assignment.subject', 'assignment.guru'])
            ->where('siswa_id', $siswaId)
            ->whereNotNull('score')
            ->when($kelasId, fn($q) => $q->whereHas('assignment', fn($s) => $s->where('kelas_id', $kelasId)))
            ->latest()
            ->paginate(10, ['*'], 'assignment_page');

        $stats = $this->calculateStats($siswaId, $kelasId);

        return view('siswa.nilai.index', compact('practicalScores', 'assignmentScores', 'stats'));
    }

    public function practical(): View
    {
        [$siswaId, $kelasId] = $this->getSiswaContext();

        $scores = NilaiPraktik::with(['practical.subject'])
            ->where('siswa_id', $siswaId)
            ->whereNull('criteria_id')
            ->when($kelasId, fn($q) => $q->whereHas('practical', fn($s) => $s->where('kelas_id', $kelasId)))
            ->latest()
            ->paginate(15);

        $col   = $scores->getCollection();
        $stats = [
            'average_score' => round($col->avg('score') ?? 0, 1),
            'total_scores'  => $col->count(),
            'highest_score' => $col->max('score') ?? 0,
            'lowest_score'  => $col->min('score') ?? 0,
        ];

        return view('siswa.nilai.practical', compact('scores', 'stats'));
    }

    /** Alias backward compat */
    public function practicalScores(): View { return $this->practical(); }

    public function assignment(): View
    {
        [$siswaId, $kelasId] = $this->getSiswaContext();

        $scores = AssignmentSubmission::with(['assignment.subject', 'assignment.guru'])
            ->where('siswa_id', $siswaId)
            ->whereNotNull('score')
            ->when($kelasId, fn($q) => $q->whereHas('assignment', fn($s) => $s->where('kelas_id', $kelasId)))
            ->latest()
            ->paginate(15);

        $col   = $scores->getCollection();
        $stats = [
            'average_score' => round($col->avg('score') ?? 0, 1),
            'total_graded'  => $col->count(),
            'highest_score' => $col->max('score') ?? 0,
            'lowest_score'  => $col->min('score') ?? 0,
        ];

        return view('siswa.nilai.assignment', compact('scores', 'stats'));
    }

    /** Alias backward compat */
    public function assignmentScores(): View { return $this->assignment(); }

    public function exportScores()
    {
        [$siswaId, $kelasId] = $this->getSiswaContext();
        $user = Auth::user();

        $practicalScores = NilaiPraktik::with(['practical.subject'])
            ->where('siswa_id', $siswaId)
            ->whereNull('criteria_id')
            ->when($kelasId, fn($q) => $q->whereHas('practical', fn($s) => $s->where('kelas_id', $kelasId)))
            ->get();

        $assignmentScores = AssignmentSubmission::with(['assignment.subject'])
            ->where('siswa_id', $siswaId)
            ->whereNotNull('score')
            ->when($kelasId, fn($q) => $q->whereHas('assignment', fn($s) => $s->where('kelas_id', $kelasId)))
            ->get();

        $filename = 'nilai-' . $siswaId . '-' . now()->format('Ymd') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($practicalScores, $assignmentScores) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Jenis', 'Judul', 'Mata Pelajaran', 'Nilai', 'Grade', 'Tanggal']);

            foreach ($practicalScores as $s) {
                $score = (float)($s->score ?? 0);
                $grade = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 70 ? 'C' : ($score >= 60 ? 'D' : 'E')));
                fputcsv($handle, [
                    'Praktikum',
                    $s->practical?->title ?? '—',
                    $s->practical?->subject?->name ?? '—',
                    $score, $grade,
                    $s->graded_at?->format('d/m/Y') ?? $s->created_at?->format('d/m/Y') ?? '—',
                ]);
            }

            foreach ($assignmentScores as $s) {
                $score = (float)($s->score ?? 0);
                $grade = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 70 ? 'C' : ($score >= 60 ? 'D' : 'E')));
                fputcsv($handle, [
                    'Tugas',
                    $s->assignment?->title ?? '—',
                    $s->assignment?->subject?->nama ?? $s->assignment?->subject?->name ?? '—',
                    $score, $grade,
                    $s->updated_at?->format('d/m/Y') ?? '—',
                ]);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    public function getChartData(): JsonResponse
    {
        [$siswaId, $kelasId] = $this->getSiswaContext();

        $practicalData = NilaiPraktik::where('siswa_id', $siswaId)
            ->when($kelasId, fn($q) => $q->whereHas('practical', fn($s) => $s->where('kelas_id', $kelasId)))
            ->selectRaw('DATE(created_at) as date, AVG(score) as average_score')
            ->groupBy('date')->orderBy('date')->get();

        $assignmentData = AssignmentSubmission::where('siswa_id', $siswaId)
            ->whereNotNull('score')
            ->when($kelasId, fn($q) => $q->whereHas('assignment', fn($s) => $s->where('kelas_id', $kelasId)))
            ->selectRaw('DATE(created_at) as date, AVG(score) as average_score')
            ->groupBy('date')->orderBy('date')->get();

        return response()->json([
            'practical_scores'  => $practicalData,
            'assignment_scores' => $assignmentData,
        ]);
    }

    // ── Protected helpers ─────────────────────────────────────────────────────

    protected function calculateStats(int $siswaId, ?int $kelasId): array
    {
        $practicals = NilaiPraktik::where('siswa_id', $siswaId)
            ->whereNull('criteria_id')
            ->when($kelasId, fn($q) => $q->whereHas('practical', fn($s) => $s->where('kelas_id', $kelasId)))
            ->get();

        $assignments = AssignmentSubmission::where('siswa_id', $siswaId)
            ->whereNotNull('score')
            ->when($kelasId, fn($q) => $q->whereHas('assignment', fn($s) => $s->where('kelas_id', $kelasId)))
            ->get();

        $pAvg = (float)($practicals->avg('score') ?? 0);
        $aAvg = (float)($assignments->avg('score') ?? 0);
        $pCnt = $practicals->count();
        $aCnt = $assignments->count();
        $tot  = $pCnt + $aCnt;

        return [
            'practical_avg'            => round($pAvg, 1),
            'assignment_avg'           => round($aAvg, 1),
            'overall_avg'              => $tot > 0 ? round((($pAvg * $pCnt) + ($aAvg * $aCnt)) / $tot, 1) : 0,
            'total_practical_scores'   => $pCnt,
            'total_graded_assignments' => $aCnt,
        ];
    }
}
