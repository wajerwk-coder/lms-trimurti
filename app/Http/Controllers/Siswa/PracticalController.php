<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Practical;
use App\Models\NilaiPraktik;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PracticalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    /**
     * Display a listing of practicals.
     */
    public function index(Request $request): View
    {
        $ucId    = Auth::id();
        $student = Siswa::where('user_id', $ucId)->first();
        $kelasId = $student?->kelas_id;
        $status  = $request->get('status', '');
        $search  = $request->get('search', '');

        $practicals = Practical::where('is_published', true)
            ->where(function ($q) use ($kelasId) {
                if ($kelasId) {
                    $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
                } else {
                    $q->whereNull('kelas_id');
                }
            })
            ->with([
                'subject',
                'kelas',
                'guru',
                'scores' => fn($q) => $q->where('siswa_id', $ucId)->whereNull('criteria_id'),
            ])
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($status === 'upcoming', fn($q) => $q->where('due_date', '>', now()))
            ->when($status === 'past',     fn($q) => $q->where('due_date', '<=', now()))
            ->when($status === 'graded',   fn($q) => $q->whereHas('scores', fn($s) => $s->where('siswa_id', $ucId)->whereNull('criteria_id')))
            ->when($status === 'ungraded', fn($q) => $q->whereDoesntHave('scores', fn($s) => $s->where('siswa_id', $ucId)->whereNull('criteria_id')))
            ->orderBy('due_date', 'asc')
            ->paginate(12);

        // Stats dari aggregate — bukan dari paginator
        $baseQ   = Practical::where('is_published', true)
            ->where(function ($q) use ($kelasId) {
                if ($kelasId) {
                    $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
                } else {
                    $q->whereNull('kelas_id');
                }
            });

        $totalAll      = (clone $baseQ)->count();
        $gradedCount   = (clone $baseQ)->whereHas('scores', fn($s) => $s->where('siswa_id', $ucId)->whereNull('criteria_id'))->count();
        $upcomingCount = (clone $baseQ)->where('due_date', '>', now())->count();

        $avgScore = \App\Models\NilaiPraktik::where('siswa_id', $ucId)
            ->whereNull('criteria_id')
            ->whereNotNull('score')
            ->avg('score');
        $averageScore = $avgScore ? round($avgScore, 1) : 0;

        return view('siswa.praktikum.index', compact(
            'practicals', 'status', 'search',
            'totalAll', 'gradedCount', 'upcomingCount', 'averageScore'
        ));
    }

    /**
     * Display the specified practical.
     */
    public function show($id): View
    {
        $student = Siswa::where('user_id', Auth::id())->first();
        $kelasId = $student->kelas_id ?? null;

        $practical = Practical::where('is_published', true)
            ->where(function ($query) use ($kelasId) {
                if ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                          ->orWhereNull('kelas_id');
                } else {
                    $query->whereNull('kelas_id');
                }
            })
            ->with(['guru', 'kelas'])
            ->findOrFail($id);

        $scores = PracticalScore::with('criteria')
            ->where('practical_id', $id)
            ->where('siswa_id', Auth::id())
            ->get();

        $averageScore = $scores->avg('score') ?? 0;
        $isGraded = $scores->isNotEmpty();
        $totalCriteria = Criteria::where('is_active', true)->count();

        return view('siswa.praktikum.show', compact(
            'practical',
            'scores',
            'averageScore',
            'isGraded',
            'totalCriteria'
        ));
    }

    /**
     * Display practical score history.
     */
    public function history(): View
    {
        $scores = PracticalScore::with(['practical', 'criteria'])
            ->where('siswa_id', Auth::id())
            ->latest()
            ->paginate(15);

        // ✅ Optimasi: Gunakan collection
        $scoreCollection = $scores->getCollection();
        $averageScore = $scoreCollection->avg('score') ?? 0;
        $highestScore = $scoreCollection->max('score') ?? 0;
        $lowestScore = $scoreCollection->min('score') ?? 0;

        $stats = [
            'total_graded' => $scores->total(),
            'average_score' => $averageScore,
            'highest_score' => $highestScore,
            'lowest_score' => $lowestScore,
        ];

        return view('siswa.praktikum.history', compact('scores', 'stats'));
    }

    /**
     * Get scores for a practical (AJAX).
     */
    public function getScores($practicalId): JsonResponse
    {
        $student = Siswa::where('user_id', Auth::id())->first();
        $kelasId = $student->kelas_id ?? null;

        $practical = Practical::where('is_published', true)
            ->where(function ($query) use ($kelasId) {
                if ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                          ->orWhereNull('kelas_id');
                } else {
                    $query->whereNull('kelas_id');
                }
            })
            ->findOrFail($practicalId);

        $scores = PracticalScore::with('criteria')
            ->where('practical_id', $practicalId)
            ->where('siswa_id', Auth::id())
            ->get();

        return response()->json([
            'scores' => $scores,
            'average' => $scores->avg('score') ?? 0,
            'total_criteria' => $scores->count(),
            'is_graded' => $scores->isNotEmpty()
        ]);
    }

    /**
     * Get practical progress (AJAX).
     */
    public function getProgress(): JsonResponse
    {
        $student = Siswa::where('user_id', Auth::id())->first();
        $kelasId = $student->kelas_id ?? null;

        $totalPracticals = Practical::where('is_published', true)
            ->where(function ($query) use ($kelasId) {
                if ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                          ->orWhereNull('kelas_id');
                } else {
                    $query->whereNull('kelas_id');
                }
            })
            ->count();

        $gradedPracticals = PracticalScore::where('siswa_id', Auth::id())
            ->whereIn('practical_id', function($query) {
                $query->select('id')
                    ->from('practicals')
                    ->where('is_published', true);
            })
            ->distinct('practical_id')
            ->count('practical_id');

        $progress = $totalPracticals > 0 ? round(($gradedPracticals / $totalPracticals) * 100) : 0;

        return response()->json([
            'total' => $totalPracticals,
            'graded' => $gradedPracticals,
            'progress' => $progress,
            'remaining' => $totalPracticals - $gradedPracticals
        ]);
    }

    /**
     * Get upcoming practicals (AJAX).
     */
    public function upcoming(): JsonResponse
    {
        $student = Siswa::where('user_id', Auth::id())->first();
        $kelasId = $student->kelas_id ?? null;

        $upcomingPracticals = Practical::where('is_published', true)
            ->where(function ($query) use ($kelasId) {
                if ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                          ->orWhereNull('kelas_id');
                } else {
                    $query->whereNull('kelas_id');
                }
            })
            ->where('date', '>=', now())
            ->whereDoesntHave('scores', function($query) {
                $query->where('siswa_id', Auth::id());
            })
            ->orderBy('date', 'asc')
            ->take(5)
            ->get();

        return response()->json($upcomingPracticals);
    }
}
