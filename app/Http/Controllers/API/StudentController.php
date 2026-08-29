<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * Get students by subject and class
     */
    public function getStudentsBySubjectAndClass(Request $request): JsonResponse
    {
        try {
            $subjectId = $request->get('subject_id');
            $classId = $request->get('class');
            
            // Validation
            if (!$subjectId || !$classId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject ID dan Class ID diperlukan'
                ], 400);
            }
            
            // Get students from the specified class (kelas_id is in siswa table)
            $students = Siswa::where('kelas_id', $classId)
                ->orderBy('name')
                ->get(['id', 'name', 'nis', 'kelas_id', 'user_id']);
            
            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada siswa di kelas ini'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'students' => $students,
                'total' => $students->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
