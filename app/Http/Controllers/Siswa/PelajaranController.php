<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Kelas;
use App\Models\Assignment;
use App\Models\Material;
use App\Models\Practical;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PelajaranController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    // ── Helper: ambil profil siswa + kelas_id ────────────────────────────

    private function getSiswaProfile(): array
    {
        $user = Auth::user();
        $siswaProfile = Siswa::with(['kelas.jurusan', 'user'])
            ->where('user_id', $user->id)
            ->first();

        return [
            'profile'  => $siswaProfile,
            'kelasId'  => $siswaProfile?->kelas_id,
            'kelas'    => $siswaProfile?->kelas,
        ];
    }

    // ── Index: daftar mata pelajaran ─────────────────────────────────────

    public function index(): View
    {
        ['profile' => $siswaProfile, 'kelasId' => $kelasId, 'kelas' => $kelas] = $this->getSiswaProfile();

        // Ambil subject_id yang ada di class_subjects untuk kelas siswa
        $subjectIds = [];
        if ($kelasId) {
            $subjectIds = DB::table('class_subjects')
                ->where('class_id', $kelasId)   // kolom class_id, bukan kelas_id
                ->pluck('subject_id')
                ->toArray();
        }

        // Query subjects — jika ada class_subjects, filter; jika tidak tampilkan semua aktif
        $subjectsQuery = Subject::where('is_active', true)->orderBy('name');
        if (!empty($subjectIds)) {
            $subjectsQuery->whereIn('id', $subjectIds);
        }
        $subjects = $subjectsQuery->get();

        // Enrich: hitung materi, tugas, praktikum per subject
        $subjects->each(function ($subject) use ($kelasId) {
            $baseM = Material::where('subject_id', $subject->id)->whereNotNull('published_at');
            $baseA = Assignment::where('subject_id', $subject->id)->where('is_published', true);
            $baseP = Practical::where('subject_id', $subject->id)->where('is_published', true);

            if ($kelasId) {
                $baseM->where(fn($q) => $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id'));
                $baseA->where(fn($q) => $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id'));
                $baseP->where(fn($q) => $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id'));
            }

            $subject->material_count    = (clone $baseM)->count();
            $subject->assignment_count  = (clone $baseA)->count();
            $subject->practical_count   = (clone $baseP)->count();
            $subject->total_activities  = $subject->material_count + $subject->assignment_count + $subject->practical_count;
        });

        $siswaData = [
            'name'    => Auth::user()->name,
            'kelas'   => $kelas?->name ?? 'Belum ada kelas',
            'jurusan' => $kelas?->jurusan?->name ?? '—',
        ];

        return view('siswa.pelajaran.index', compact('subjects', 'siswaData', 'kelas'));
    }

    // ── Show: detail satu mata pelajaran ────────────────────────────────

    public function show(int $id): View
    {
        ['kelasId' => $kelasId] = $this->getSiswaProfile();

        $subject = Subject::findOrFail($id);

        $ucId = Auth::id();   // users_central.id — dipakai untuk filter submission & score

        // Materi
        $materialsQ = Material::where('subject_id', $subject->id)->whereNotNull('published_at');
        if ($kelasId) {
            $materialsQ->where(fn($q) => $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id'));
        }
        $materials = $materialsQ->orderByDesc('created_at')->get();

        // Tugas — eager-load submissions milik siswa ini
        $assignmentsQ = Assignment::where('subject_id', $subject->id)->where('is_published', true);
        if ($kelasId) {
            $assignmentsQ->where(fn($q) => $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id'));
        }
        $assignments = $assignmentsQ
            ->with(['submissions' => fn($q) => $q->where('siswa_id', $ucId)])
            ->orderByDesc('created_at')
            ->get();

        // Praktikum — eager-load scores milik siswa ini (NilaiPraktik.siswa_id = uc.id)
        $practicalsQ = Practical::where('subject_id', $subject->id)->where('is_published', true);
        if ($kelasId) {
            $practicalsQ->where(fn($q) => $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id'));
        }
        $practicals = $practicalsQ
            ->with(['scores' => fn($q) => $q->where('siswa_id', $ucId)->whereNull('criteria_id')])
            ->orderByDesc('created_at')
            ->get();

        return view('siswa.pelajaran.show', compact(
            'subject', 'materials', 'assignments', 'practicals', 'kelasId'
        ));
    }
}
