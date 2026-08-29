<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class MataPelajaranController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $mataPelajarans        = MataPelajaran::orderBy('name')->get();
        $mataPelajaranTeori    = $mataPelajarans->where('type', 'teori')->count();
        $mataPelajaranPraktikum = $mataPelajarans->where('type', 'praktikum')->count();
        $mataPelajaranCampuran = $mataPelajarans->where('type', 'campuran')->count();
        $mataPelajaranAktif    = $mataPelajarans->where('is_active', true)->count();

        return view('admin.mata-pelajaran.index', compact(
            'mataPelajarans',
            'mataPelajaranTeori',
            'mataPelajaranPraktikum',
            'mataPelajaranCampuran',
            'mataPelajaranAktif'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.mata-pelajaran.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:subjects,name',
            'code'        => 'required|string|max:20|unique:subjects,code',
            'description' => 'nullable|string',
            'type'        => 'required|in:teori,praktikum,campuran',
            'sks'         => 'required|integer|min:1|max:10',
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique'   => 'Nama mata pelajaran sudah ada.',
            'code.required' => 'Kode wajib diisi.',
            'code.unique'   => 'Kode sudah digunakan.',
            'type.required' => 'Jenis mata pelajaran wajib dipilih.',
            'sks.required'  => 'SKS wajib diisi.',
        ]);

        try {
            MataPelajaran::create([
                'name'        => $request->name,
                'code'        => strtoupper($request->code),
                'description' => $request->description,
                'type'        => $request->type,
                'sks'         => $request->sks,
                'is_active'   => $request->boolean('is_active', true),
            ]);

            return redirect()->route('admin.mata-pelajaran.index')
                ->with('success', "Mata pelajaran {$request->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            Log::error('MataPelajaranController::store: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan mata pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MataPelajaran $mataPelajaran): View
    {
        return view('admin.mata-pelajaran.show', compact('mataPelajaran'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MataPelajaran $mataPelajaran): View
    {
        return view('admin.mata-pelajaran.edit', compact('mataPelajaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:subjects,name,' . $mataPelajaran->id,
            'code'        => 'required|string|max:20|unique:subjects,code,' . $mataPelajaran->id,
            'description' => 'nullable|string',
            'type'        => 'required|in:teori,praktikum,campuran',
            'sks'         => 'required|integer|min:1|max:10',
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique'   => 'Nama mata pelajaran sudah ada.',
            'code.required' => 'Kode wajib diisi.',
            'code.unique'   => 'Kode sudah digunakan.',
            'type.required' => 'Jenis mata pelajaran wajib dipilih.',
            'sks.required'  => 'SKS wajib diisi.',
        ]);

        try {
            $mataPelajaran->update([
                'name'        => $request->name,
                'code'        => strtoupper($request->code),
                'description' => $request->description,
                'type'        => $request->type,
                'sks'         => $request->sks,
                'is_active'   => $request->boolean('is_active'),
            ]);

            return redirect()->route('admin.mata-pelajaran.index')
                ->with('success', "Mata pelajaran {$mataPelajaran->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            Log::error('MataPelajaranController::update: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui mata pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        // Cegah hapus jika masih ada materi, tugas, atau praktikum terkait
        $jumlahMateri    = $mataPelajaran->materials()->count();
        $jumlahTugas     = $mataPelajaran->assignments()->count();
        $jumlahPraktikum = $mataPelajaran->practicals()->count();
        $total = $jumlahMateri + $jumlahTugas + $jumlahPraktikum;

        if ($total > 0) {
            $detail = collect([
                $jumlahMateri    > 0 ? "{$jumlahMateri} materi"    : null,
                $jumlahTugas     > 0 ? "{$jumlahTugas} tugas"      : null,
                $jumlahPraktikum > 0 ? "{$jumlahPraktikum} praktikum" : null,
            ])->filter()->implode(', ');

            return back()->with('error',
                "Tidak dapat menghapus '{$mataPelajaran->name}' karena masih memiliki {$detail}."
            );
        }

        try {
            $nama = $mataPelajaran->name;
            $mataPelajaran->delete();

            return redirect()->route('admin.mata-pelajaran.index')
                ->with('success', "Mata pelajaran '{$nama}' berhasil dihapus.");

        } catch (\Throwable $e) {
            Log::error('MataPelajaranController::destroy: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus mata pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status aktif / nonaktif.
     */
    public function toggleStatus(MataPelajaran $mataPelajaran): RedirectResponse
    {
        try {
            $mataPelajaran->update(['is_active' => !$mataPelajaran->is_active]);
            $status = $mataPelajaran->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()->route('admin.mata-pelajaran.index')
                ->with('success', "Mata pelajaran '{$mataPelajaran->name}' berhasil {$status}.");

        } catch (\Throwable $e) {
            Log::error('MataPelajaranController::toggleStatus: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Seed data mata pelajaran default.
     */
    public function seedDefault(): RedirectResponse
    {
        $defaultMapel = [
            ['name' => 'Pendidikan Agama',            'code' => 'PA',  'type' => 'teori',     'sks' => 2, 'is_active' => true],
            ['name' => 'Pendidikan Kewarganegaraan',  'code' => 'PKW', 'type' => 'teori',     'sks' => 2, 'is_active' => true],
            ['name' => 'Bahasa Indonesia',            'code' => 'BI',  'type' => 'teori',     'sks' => 4, 'is_active' => true],
            ['name' => 'Matematika',                  'code' => 'MTK', 'type' => 'teori',     'sks' => 4, 'is_active' => true],
            ['name' => 'Bahasa Inggris',              'code' => 'ENG', 'type' => 'teori',     'sks' => 4, 'is_active' => true],
            ['name' => 'Biologi',                     'code' => 'BIO', 'type' => 'teori',     'sks' => 3, 'is_active' => true],
            ['name' => 'Fisika',                      'code' => 'FIS', 'type' => 'teori',     'sks' => 3, 'is_active' => true],
            ['name' => 'Kimia',                       'code' => 'KIM', 'type' => 'teori',     'sks' => 3, 'is_active' => true],
            ['name' => 'Keperawatan Dasar',           'code' => 'KD',  'type' => 'praktikum', 'sks' => 6, 'is_active' => true],
            ['name' => 'Farmasi Dasar',               'code' => 'FD',  'type' => 'praktikum', 'sks' => 6, 'is_active' => true],
            ['name' => 'Teknologi Lab. Medik Dasar',  'code' => 'TLM', 'type' => 'campuran',  'sks' => 6, 'is_active' => true],
        ];

        try {
            $ditambahkan = 0;
            foreach ($defaultMapel as $mapel) {
                $result = MataPelajaran::firstOrCreate(['code' => $mapel['code']], $mapel);
                if ($result->wasRecentlyCreated) $ditambahkan++;
            }

            $msg = $ditambahkan > 0
                ? "{$ditambahkan} mata pelajaran default berhasil ditambahkan."
                : 'Semua mata pelajaran default sudah ada.';

            return redirect()->route('admin.mata-pelajaran.index')->with('success', $msg);

        } catch (\Throwable $e) {
            Log::error('MataPelajaranController::seedDefault: ' . $e->getMessage());
            return back()->with('error', 'Gagal menambahkan data default: ' . $e->getMessage());
        }
    }
}
