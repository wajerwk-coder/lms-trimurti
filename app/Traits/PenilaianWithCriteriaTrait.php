<?php

namespace App\Http\Controllers\Guru;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\Kelas;
use App\Models\Assignment;
use App\Models\Practical;
use App\Models\User;
use Illuminate\Support\Facades\DB;

trait PenilaianWithCriteriaTrait
{
    /**
     * Show auto assessment with criteria page.
     */
    public function autoWithCriteria(): View
    {
        $guruId = Auth::id();
        
        // Get active subjects taught by this guru
        $subjects = Subject::where('is_active', true)->get();
        
        // Get active classes
        $classes = Kelas::aktif()->get();
        
        // Get assignments and practicals by this guru
        $assignments = Assignment::where('guru_id', $guruId)
            ->where('is_published', true)
            ->with('subject')
            ->latest()
            ->get();
            
        $practicals = Practical::where('guru_id', $guruId)
            ->where('is_published', true)
            ->with('subject')
            ->latest()
            ->get();
        
        // Get students
        $students = User::where('role', 'siswa')
            ->where('is_active', true)
            ->with('kelas')
            ->orderBy('name')
            ->get();
        
        // Get all available criteria (for reference)
        $allCriteria = DB::table('criteria')->get();
        
        return view('guru.penilaian.auto_with_criteria', compact('subjects', 'classes', 'assignments', 'practicals', 'students', 'allCriteria'));
    }

    /**
     * Save auto assessment with criteria.
     */
    public function saveAutoAssessmentWithCriteria(Request $request): RedirectResponse
    {
        $guruId = Auth::id();
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'siswa_id' => 'required|exists:users,id',
            'practical_id' => 'required|exists:practicals,id',
            'criteria' => 'required|array',
            'feedback' => 'required|string|max:2000',
            'total_score' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Verify guru owns the practical
            $practical = Practical::where('id', $request->practical_id)
                ->where('guru_id', $guruId)
                ->firstOrFail();

            // Calculate weighted score based on criteria
            $criteriaWeights = [
                'prep_1' => 0.20, 'prep_2' => 0.15, 'prep_3' => 0.15,
                'exec_1' => 0.25, 'exec_2' => 0.20, 'exec_3' => 0.20,
                'result_1' => 0.30, 'result_2' => 0.20,
                'att_1' => 0.15, 'att_2' => 0.20
            ];

            $totalWeightedScore = 0;
            $checkedCriteria = $request->criteria ?? [];

            foreach ($criteriaWeights as $criterionId => $weight) {
                if (in_array($criterionId, $checkedCriteria)) {
                    $totalWeightedScore += 100 * $weight;
                }
            }

            // Create assessment record using NilaiPraktik
            $assessment = \App\Models\NilaiPraktik::updateOrCreate(
                [
                    'siswa_id' => $request->siswa_id,
                    'mata_praktik' => $practical->judul ?? 'Praktikum',
                    'tanggal_praktik' => now()->toDateString(),
                ],
                [
                    'guru_id' => $guruId,
                    'total_nilai' => $totalWeightedScore,
                    'grade' => $this->calculateGrade($totalWeightedScore),
                    'feedback_otomatis' => $request->feedback,
                    'status' => 'final',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return redirect()
                ->route('guru.penilaian.index')
                ->with('success', 'Penilaian dengan kriteria berhasil disimpan! Nilai: ' . number_format($totalWeightedScore, 1));

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal menyimpan penilaian: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Calculate grade based on score
     */
    private function calculateGrade($score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }
}
