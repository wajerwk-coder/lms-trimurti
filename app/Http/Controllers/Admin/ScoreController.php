<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NilaiPraktik;
use App\Models\AssignmentSubmission;
use App\Models\UserCentral;
use App\Models\Kelas;
use App\Models\Subject;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        // Statistik ringkasan
        $stats = [
            'total_practical'   => 0,
            'total_assignment'  => 0,
            'avg_practical'     => 0,
            'avg_assignment'    => 0,
        ];

        $practicalScores  = collect();
        $assignmentScores = collect();

        try {
            $practicalQuery = NilaiPraktik::with(['practical.subject', 'siswa'])
                ->whereNull('criteria_id')
                ->when($request->siswa_id,  fn($q) => $q->where('siswa_id', $request->siswa_id))
                ->when($request->kelas_id,  fn($q) => $q->whereHas('practical', fn($s) => $s->where('kelas_id', $request->kelas_id)))
                ->when($request->subject_id,fn($q) => $q->whereHas('practical', fn($s) => $s->where('subject_id', $request->subject_id)))
                ->latest()
                ->paginate(15, ['*'], 'p_page');

            $assignmentQuery = AssignmentSubmission::with(['assignment.subject', 'assignment.guru', 'siswa'])
                ->whereNotNull('score')
                ->when($request->siswa_id,  fn($q) => $q->where('siswa_id', $request->siswa_id))
                ->when($request->kelas_id,  fn($q) => $q->whereHas('assignment', fn($s) => $s->where('kelas_id', $request->kelas_id)))
                ->when($request->subject_id,fn($q) => $q->whereHas('assignment', fn($s) => $s->where('subject_id', $request->subject_id)))
                ->latest()
                ->paginate(15, ['*'], 'a_page');

            $stats['total_practical']  = NilaiPraktik::whereNull('criteria_id')->count();
            $stats['total_assignment'] = AssignmentSubmission::whereNotNull('score')->count();
            $stats['avg_practical']    = round(NilaiPraktik::whereNull('criteria_id')->avg('score') ?? 0, 1);
            $stats['avg_assignment']   = round(AssignmentSubmission::whereNotNull('score')->avg('score') ?? 0, 1);

            $practicalScores  = $practicalQuery;
            $assignmentScores = $assignmentQuery;

        } catch (\Throwable $e) {
            // tabel belum ada — tampilkan halaman kosong
        }

        $siswas   = UserCentral::where('role', 'siswa')->orderBy('name')->get(['id','name']);
        $kelas    = Kelas::orderBy('name')->get(['id','name']);
        $subjects = Subject::where('is_active', true)->orderBy('name')->get(['id','name']);

        return view('admin.scores.index', compact(
            'stats', 'practicalScores', 'assignmentScores',
            'siswas', 'kelas', 'subjects'
        ));
    }
}
