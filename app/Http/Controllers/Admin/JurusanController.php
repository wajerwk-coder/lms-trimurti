<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        $jurusan = Jurusan::withCount('kelas')->orderBy('name')->get();
        return view('admin.jurusan.index', compact('jurusan'));
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

            // Sinkronkan ke tabel majors (FK lama) agar tambah kelas tidak gagal
            $this->syncToMajors($jurusan);

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

            // Sinkronkan perubahan ke tabel majors
            $this->syncToMajors($jurusan);

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

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Sinkronkan jurusan ke tabel majors (FK lama classes.major_id → majors).
     * Dipanggil setiap store/update agar tambah kelas tidak kena FK violation.
     */
    private function syncToMajors(Jurusan $jurusan): void
    {
        $code = $jurusan->code ?? strtoupper(substr($jurusan->name, 0, 4));

        $exists = DB::table('majors')->where('id', $jurusan->id)->exists();

        if ($exists) {
            DB::table('majors')->where('id', $jurusan->id)->update([
                'name'        => $jurusan->name,
                'code'        => $code,
                'description' => $jurusan->description,
                'updated_at'  => now(),
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
    }
}
