<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ── Index ────────────────────────────────────────────────────────────────

    public function index(): View
    {
        // Gunakan withCount + raw subquery agar cepat (tidak load semua data siswa)
        $kelas = Kelas::with('jurusan')
            ->withCount([
                'siswa as siswa_count' => fn($q) => $q->whereNull('deleted_at'),
            ])
            ->orderBy('name')
            ->get();

        $totalSiswa = $kelas->sum('siswa_count');

        // Stats per jurusan (2 terbesar)
        $perJurusan   = $kelas->groupBy('jurusan_id')
            ->map(fn($g) => ['name' => $g->first()->jurusan?->name ?? 'Lainnya', 'count' => $g->count()])
            ->sortByDesc('count')
            ->values();

        return view('admin.kelas.index', [
            'kelas'            => $kelas,
            'totalSiswa'       => $totalSiswa,
            'kelasKeperawatan' => $perJurusan->get(0)['count'] ?? 0,
            'namaJurusan1'     => $perJurusan->get(0)['name']  ?? 'Jurusan 1',
            'kelasFarmasi'     => $perJurusan->get(1)['count'] ?? 0,
            'namaJurusan2'     => $perJurusan->get(1)['name']  ?? 'Jurusan 2',
            'jurusanList'      => Jurusan::orderBy('name')->get(), // untuk filter dropdown
        ]);
    }

    // ── Create & Store ────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('admin.kelas.create', [
            'jurusans' => Jurusan::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => 'required|string|max:100|unique:classes,name',
            'grade'         => 'required|in:X,XI,XII',
            'major_id'      => 'required|exists:jurusans,id',
            'academic_year' => 'required|string|max:20',
            'status'        => 'nullable|in:active,inactive',
        ], [
            'name.required'          => 'Nama kelas wajib diisi.',
            'name.unique'            => 'Nama kelas sudah ada.',
            'grade.required'         => 'Tingkat kelas wajib dipilih.',
            'grade.in'               => 'Tingkat harus X, XI, atau XII.',
            'major_id.required'      => 'Jurusan wajib dipilih.',
            'major_id.exists'        => 'Jurusan tidak ditemukan.',
            'academic_year.required' => 'Tahun ajaran wajib diisi.',
        ]);

        try {
            $jurusan = Jurusan::findOrFail($request->major_id);

            Kelas::create([
                'name'          => $request->name,
                'grade'         => $request->grade,
                'jurusan_id'    => $jurusan->id,
                'academic_year' => $request->academic_year,
                'status'        => $request->status ?? 'active',
            ]);

            return redirect()->route('admin.kelas.index')
                ->with('success', 'Kelas ' . $request->name . ' berhasil ditambahkan.');

        } catch (\Throwable $e) {
            Log::error('KelasController::store: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan kelas: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Kelas $kelas): View
    {
        $kelas->load(['jurusan', 'siswa.user']);
        return view('admin.kelas.show', compact('kelas'));
    }

    // ── Edit & Update ─────────────────────────────────────────────────────────

    public function edit(Kelas $kelas): View
    {
        // Hitung siswa via query langsung — tidak load semua data
        $siswaCount = DB::table('siswa')
            ->where('kelas_id', $kelas->id)
            ->whereNull('deleted_at')
            ->count();

        return view('admin.kelas.edit', [
            'kelas'      => $kelas->load('jurusan'),
            'jurusans'   => Jurusan::orderBy('name')->get(),
            'siswaCount' => $siswaCount,
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $request->validate([
            'name'          => 'required|string|max:100|unique:classes,name,' . $kelas->id,
            'grade'         => 'required|in:X,XI,XII',
            'major_id'      => 'required|exists:jurusans,id',
            'academic_year' => 'required|string|max:20',
            'status'        => 'nullable|in:active,inactive',
        ], [
            'name.required'          => 'Nama kelas wajib diisi.',
            'name.unique'            => 'Nama kelas sudah ada.',
            'grade.required'         => 'Tingkat kelas wajib dipilih.',
            'grade.in'               => 'Tingkat harus X, XI, atau XII.',
            'major_id.required'      => 'Jurusan wajib dipilih.',
            'major_id.exists'        => 'Jurusan tidak ditemukan.',
            'academic_year.required' => 'Tahun ajaran wajib diisi.',
        ]);

        try {
            $jurusan = Jurusan::findOrFail($request->major_id);

            $kelas->update([
                'name'          => $request->name,
                'grade'         => $request->grade,
                'jurusan_id'    => $jurusan->id,
                'academic_year' => $request->academic_year,
                'status'        => $request->status ?? 'active',
            ]);

            return redirect()->route('admin.kelas.index')
                ->with('success', 'Kelas ' . $request->name . ' berhasil diperbarui.');

        } catch (\Throwable $e) {
            Log::error('KelasController::update: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui kelas: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $siswaCount = DB::table('siswa')
            ->where('kelas_id', $kelas->id)
            ->whereNull('deleted_at')
            ->count();

        if ($siswaCount > 0) {
            return back()->with('error',
                "Tidak dapat menghapus kelas '{$kelas->name}' karena masih ada {$siswaCount} siswa aktif. " .
                "Pindahkan atau hapus siswa terlebih dahulu."
            );
        }

        try {
            $nama = $kelas->name;

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Hapus semua data turunan yang punya FK ke classes.id
            if (\Illuminate\Support\Facades\Schema::hasTable('class_subjects')) {
                // Ambil ID class_subjects dulu untuk hapus turunannya
                $classSubjectIds = DB::table('class_subjects')
                    ->where('class_id', $kelas->id)
                    ->pluck('id');

                if ($classSubjectIds->isNotEmpty()) {
                    DB::table('materials')->whereIn('class_subject_id', $classSubjectIds)->delete();
                    DB::table('assignments')->whereIn('class_subject_id', $classSubjectIds)->delete();
                    DB::table('attendances')->whereIn('class_subject_id', $classSubjectIds)->delete();
                    if (\Illuminate\Support\Facades\Schema::hasTable('practicals')) {
                        DB::table('practicals')->whereIn('class_subject_id', $classSubjectIds)->delete();
                    }
                }

                DB::table('class_subjects')->where('class_id', $kelas->id)->delete();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('class_students')) {
                DB::table('class_students')->where('class_id', $kelas->id)->delete();
            }

            // Lepas FK siswa yang sudah soft-deleted dari kelas ini
            DB::table('siswa')->where('kelas_id', $kelas->id)->update(['kelas_id' => null]);

            // Hapus jadwal ujian yang merujuk ke kelas ini
            if (\Illuminate\Support\Facades\Schema::hasTable('exam_schedules_new')) {
                DB::table('exam_schedules_new')->where('kelas_id', $kelas->id)->delete();
            }

            // Hapus practicals langsung yang merujuk ke kelas ini
            if (\Illuminate\Support\Facades\Schema::hasTable('practicals')) {
                DB::table('practicals')->where('kelas_id', $kelas->id)->delete();
            }

            $kelas->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->route('admin.kelas.index')
                ->with('success', "Kelas '{$nama}' berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            Log::error('KelasController::destroy: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────
    // (sync ke majors sudah tidak diperlukan setelah drop tabel majors)
}
