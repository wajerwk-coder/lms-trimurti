<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\Practical;
use App\Models\AssignmentSubmission;

class GuruStatsComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Only compose for authenticated guru users
        if (!Auth::check() || Auth::user()->role !== 'guru') {
            return;
        }

        $guruId = Auth::id();

        $stats = [
            'total_materials' => Material::where('guru_id', $guruId)->count(),
            'total_assignments' => Assignment::where('guru_id', $guruId)->count(),
            'total_practicals' => Practical::where('guru_id', $guruId)->count(),
            'total_students' => DB::table('users_central')->where('role', 'siswa')->count(),
            'pending_grading' => AssignmentSubmission::join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->where('assignments.guru_id', $guruId)
                ->whereNull('assignment_submissions.score')
                ->count(),
            'pending_submissions' => AssignmentSubmission::join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->where('assignments.guru_id', $guruId)
                ->whereNull('assignment_submissions.score')
                ->count(),
        ];

        $view->with('stats', $stats);
    }
}