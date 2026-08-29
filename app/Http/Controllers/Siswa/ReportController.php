<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\AssignmentSubmission;
use App\Models\PracticalScore;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    /**
     * Display student reports and grades
     */
    public function index(): View
    {
        $user    = Auth::user();
        $ucId    = $user->id; // users_central.id — FK yang dipakai di submissions, attendance, dll

        $student = Siswa::where('user_id', $ucId)->first();

        // Assignment submissions: FK siswa_id → users_central.id
        $assignmentSubmissions = AssignmentSubmission::where('siswa_id', $ucId)
            ->with('assignment.subject')
            ->get();

        // Practical scores: FK siswa_id → users_central.id, criteria_id IS NULL = summary
        $practicalScores = \App\Models\NilaiPraktik::where('siswa_id', $ucId)
            ->whereNull('criteria_id')
            ->with('practical.subject')
            ->get();

        // Attendance: FK siswa_id → users_central.id
        $attendances = Attendance::where('siswa_id', $ucId)
            ->orderBy('date', 'desc')
            ->get();

        // Statistics
        $totalAssignments  = $assignmentSubmissions->count();
        $gradedAssignments = $assignmentSubmissions->whereNotNull('score')->count();
        $averageScore      = $assignmentSubmissions->whereNotNull('score')->avg('score');
        
        $totalPracticals = $practicalScores->count();
        $gradedPracticals = $practicalScores->whereNotNull('score')->count();
        $averagePracticalScore = $practicalScores->whereNotNull('score')->avg('score');
        
        $totalAttendances = $attendances->count();
        $presentAttendances = $attendances->where('status', 'hadir')->count();
        
        return view('siswa.reports.index', compact(
            'student',
            'assignmentSubmissions',
            'practicalScores',
            'attendances',
            'totalAssignments',
            'gradedAssignments',
            'averageScore',
            'totalPracticals',
            'gradedPracticals',
            'averagePracticalScore',
            'totalAttendances',
            'presentAttendances'
        ));
    }
}