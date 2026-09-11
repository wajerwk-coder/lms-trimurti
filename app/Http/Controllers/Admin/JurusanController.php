<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class JurusanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $jurusan = Jurusan::withCount(['kelas', 'siswa'])->orderBy('name')->get();

        $totalJurusan = $jurusan->count();
        $totalKelas   = $jurusan->sum('kelas_count');
        $totalSiswa   = $jurusan->sum('siswa_count');
        $jurusanAktif = $jurusan->where('is_active', true)->count();

        return view('admin.jurusan.index', compact(
            'jurusan', 'totalJurusan', 'totalKelas', 'totalSiswa', 'jurusanAktif'
        ));
    }

    public function create(): View
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:jurusans,name',
            'code'        => 'required|string|max:20|unique:jurusans,code',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ], [
            'name.required' => 'Nama jurusan wajib diisi.',
            'name.unique'   => 'Nama jurusan sudah ada.',
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique'   => 'Kode jurusan sudah ada.',
        ]);

        try {
            $jurusan = Jurusan::create([
                'name'        => $request->name,
                'code'        => strtoupper($request->code),
                'description' => $request->description,
                'is_active'   => $request->boolean('is_active', true),
            ]);

            $returnTo = $request->input('return_to');
            if ($returnTo) {
                return redirect($returnTo)->with('success', "Jurusan {$jurusan->name} berhasil ditambahkan.");
            }

            return redirect()->route('admin.jurusan.index')
                ->with('success', "Jurusan {$jurusan->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            Log::error('JurusanController::store: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan jurusan: ' . $e->getMessage());
        }
    }

    public function show(Jurusan $jurusan): View
    {
        $jurusan->load('kelas');
        return view('admin.jurusan.show', compact('jurusan'));
    }

    public function edit(Jurusan $jurusan): View
    {
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:jurusans,name,' . $jurusan->id,
            'code'        => 'required|string|max:20|unique:jurusans,code,' . $jurusan->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ], [
            'name.required' => 'Nama jurusan wajib diisi.',
            'name.unique'   => 'Nama jurusan sudah ada.',
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique'   => 'Kode jurusan sudah ada.',
        ]);

        try {
            $jurusan->update([
                'name'        => $request->name,
                'code'        => strtoupper($request->code),
                'description' => $request->description,
                'is_active'   => $request->boolean('is_active', true),
            ]);

            return redirect()->route('admin.jurusan.index')
                ->with('success', "Jurusan {$jurusan->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            Log::error('JurusanController::update: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui jurusan: ' . $e->getMessage());
        }
    }

    public function destroy(Jurusan $jurusan): RedirectResponse
    {
        if ($jurusan->kelas()->count() > 0) {
            return back()->with('error',
                "Tidak dapat menghapus jurusan '{$jurusan->name}' yang masih memiliki kelas."
            );
        }

        $nama = $jurusan->name;
        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')
            ->with('success', "Jurusan '{$nama}' berhasil dihapus.");
    }
}
