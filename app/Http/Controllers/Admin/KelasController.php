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
            $majorId = $this->syncMajorFromJurusan($jurusan);

            Kelas::create([
                'name'          => $request->name,
                'grade'         => $request->grade,
                'major_id'      => $majorId,
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

        $jurusan = Jurusan::findOrFail($request->major_id);
        $majorId = $this->syncMajorFromJurusan($jurusan);

        $kelas->update([
            'name'          => $request->name,
            'grade'         => $request->grade,
            'major_id'      => $majorId,
            'jurusan_id'    => $jurusan->id,
            'academic_year' => $request->academic_year,
            'status'        => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas ' . $request->name . ' berhasil diperbarui.');
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
                "Tidak dapat menghapus kelas '{$kelas->name}' karena masih ada {$siswaCount} siswa. " .
                "Pindahkan atau hapus siswa terlebih dahulu."
            );
        }

        try {
            $nama = $kelas->name;
            DB::table('class_subjects')->where('class_id', $kelas->id)->delete();
            $kelas->delete();

            return redirect()->route('admin.kelas.index')
                ->with('success', "Kelas '{$nama}' berhasil dihapus.");

        } catch (\Throwable $e) {
            Log::error('KelasController::destroy: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Sinkronkan jurusan ke tabel majors (FK lama classes.major_id → majors).
     * Pastikan record ada di majors sebelum INSERT ke classes agar FK tidak gagal.
     * Jika code null, fallback ke string kosong (majors.code NOT NULL).
     */
    private function syncMajorFromJurusan(Jurusan $jurusan): int
    {
        $code = $jurusan->code ?? strtoupper(substr($jurusan->name, 0, 4));

        $exists = DB::table('majors')->where('id', $jurusan->id)->exists();

        if ($exists) {
            DB::table('majors')->where('id', $jurusan->id)->update([
                'name'       => $jurusan->name,
                'code'       => $code,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('majors')->insert([
                'id'          => $jurusan->id,
                'name'        => $jurusan->name,
                'code'        => $code,
                'description' => $jurusan->description,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return $jurusan->id;
    }
}
