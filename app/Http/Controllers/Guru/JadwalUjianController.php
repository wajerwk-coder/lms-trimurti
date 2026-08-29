<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\Kelas;
use Illuminate\Http\Request;

class JadwalUjianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'guru']);
    }

    public function index(Request $request)
    {
        $schedules = ExamSchedule::with(['subject', 'kelas', 'creator'])
            ->where('is_published', true)
            ->when($request->get('search'), fn($q, $s) =>
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
            )
            ->when($request->get('exam_type'), fn($q, $t) => $q->where('exam_type', $t))
            ->when($request->get('kelas_id'),  fn($q, $k) => $q->where('kelas_id', $k))
            ->latest('start_time')
            ->paginate(15);

        $kelas = Kelas::orderBy('name')->get();

        return view('shared.jadwal-ujian.index', compact('schedules', 'kelas'));
    }
}
