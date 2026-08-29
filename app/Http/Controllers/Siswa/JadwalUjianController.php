<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalUjianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    public function index(Request $request)
    {
        // Profil siswa → kelas_id
        $siswa   = Siswa::where('user_id', Auth::id())->first();
        $kelasId = $siswa?->kelas_id;

        $schedules = ExamSchedule::with(['subject', 'kelas'])
            ->where('is_published', true)
            // Filter kelas: jadwal untuk kelas siswa atau semua kelas
            ->when($kelasId, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('kelas_id', $kelasId)->orWhereNull('kelas_id')
                )
            )
            // Search: title atau description (dalam group tersendiri agar tidak break filter kelas)
            ->when($request->filled('search'), fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('title', 'like', '%'.$request->search.'%')
                       ->orWhere('description', 'like', '%'.$request->search.'%')
                )
            )
            ->when($request->filled('exam_type'), fn($q) =>
                $q->where('exam_type', $request->exam_type)
            )
            ->orderBy('start_time', 'asc') // urut dari yang paling dekat
            ->paginate(12);

        $kelas = Kelas::orderBy('name')->get();

        return view('shared.jadwal-ujian.index', compact('schedules', 'kelas'));
    }
}
