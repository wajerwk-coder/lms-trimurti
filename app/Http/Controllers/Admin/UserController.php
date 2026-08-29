<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserCentral;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Assignment;
use App\Models\Material;
use App\Models\Practical;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\NilaiPraktik;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // ── Admin CRUD (resource route) ────────────────────────────────────────────

    /**
     * Daftar admin.
     */
    public function index(): View
    {
        $users = UserCentral::where('role', 'admin')
            ->latest()
            ->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Form tambah admin.
     */
    public function create(): View
    {
        return view('admin.users.create-admin');
    }

    /**
     * Simpan admin baru — delegate ke ModernUserController::storeAdmin()
     * agar logika validasi dan penyimpanan tetap di satu tempat.
     */
    public function store(Request $request): RedirectResponse
    {
        $modernController = app(ModernUserController::class);
        return $modernController->storeAdmin($request);
    }

    /**
     * Detail user.
     */
    public function show(string $id): View
    {
        $user = UserCentral::with(['guruProfile', 'siswaProfile.kelas'])->findOrFail($id);

        $stats = [];
        if ($user->isGuru()) {
            $stats = [
                'Materi'      => Material::where('guru_id', $user->id)->count(),
                'Tugas'       => Assignment::where('guru_id', $user->id)->count(),
                'Praktikum'   => Practical::where('guru_id', $user->id)->count(),
            ];
        } elseif ($user->isSiswa()) {
            $totalAtt   = Attendance::where('siswa_id', $user->id)->count();
            $presentAtt = Attendance::where('siswa_id', $user->id)->where('status', 'hadir')->count();

            $stats = [
                'Tugas Dikumpulkan' => AssignmentSubmission::where('student_id', $user->id)->count(),
                'Kehadiran (%)'     => $totalAtt > 0 ? round($presentAtt * 100 / $totalAtt) : 0,
                'Nilai Praktikum'   => NilaiPraktik::where('siswa_id', $user->id)->whereNotNull('score')->count(),
                'Rata-rata Praktik' => round((float)(NilaiPraktik::where('siswa_id', $user->id)->avg('score') ?? 0), 1),
            ];
        }

        $activities = collect();
        try {
            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                $activities = \Spatie\Activitylog\Models\Activity::causedBy($user)->latest()->limit(10)->get();
            }
        } catch (\Throwable $e) {
            // activitylog tidak tersedia
        }

        return view('admin.users.show', compact('user', 'stats', 'activities'));
    }

    /**
     * Form edit user — arahkan ke view yang benar berdasar role.
     */
    public function edit(string $id): View
    {
        $user = UserCentral::findOrFail($id);

        return match ($user->role) {
            'guru'  => view('admin.users.edit-guru', [
                            'user'     => $user,
                            'subjects' => \App\Models\Subject::orderBy('name')->get(),
                       ]),
            'siswa' => view('admin.users.edit-siswa', [
                            'user'     => $user,
                            'kelas'    => Kelas::orderBy('name')->get(),
                            'jurusans' => Jurusan::orderBy('name')->get(),
                       ]),
            default => view('admin.users.edit-admin', compact('user')),
        };
    }

    /**
     * Perbarui user — gunakan ModernUserController::update() untuk guru/siswa,
     * atau proses sendiri untuk admin.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = UserCentral::findOrFail($id);

        if (in_array($user->role, ['guru', 'siswa'])) {
            // Delegasikan ke ModernUserController yang punya logika profil lengkap
            $modernController = app(\App\Http\Controllers\Admin\ModernUserController::class);
            return $modernController->update($request, $id);
        }

        // Update admin
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users_central,email,' . $id,
            'username' => 'required|string|max:255|unique:users_central,username,' . $id,
            'password' => 'nullable|min:8|confirmed',
        ], [
            'email.unique'       => 'Email sudah digunakan akun lain.',
            'username.unique'    => 'Username sudah digunakan.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $data = [
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'phone'    => $request->phone,
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Admin berhasil diperbarui.');
    }

    /**
     * Hapus user.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $user = UserCentral::findOrFail($id);

            // Jangan hapus diri sendiri
            if ((int)$id === auth()->id()) {
                return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
            }

            $role = $user->role;
            $user->delete();

            $redirect = match ($role) {
                'guru'  => redirect()->route('admin.users.guru'),
                'siswa' => redirect()->route('admin.users.siswa'),
                default => redirect()->route('admin.users.index'),
            };

            return $redirect->with('success', ucfirst($role) . ' berhasil dihapus.');

        } catch (\Throwable $e) {
            Log::error('UserController::destroy error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus user.');
        }
    }

    /**
     * Update status user.
     */
    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $request->validate(['status' => 'required|in:active,inactive']);

        $user = UserCentral::findOrFail($id);
        $user->update(['is_active' => $request->status === 'active']);

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    /**
     * Bulk action.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action'    => 'required|in:delete,activate,deactivate',
            'user_ids'  => 'required|array',
            'user_ids.*'=> 'exists:users_central,id',
        ]);

        DB::beginTransaction();
        try {
            $query = UserCentral::whereIn('id', $request->user_ids);
            $count = count($request->user_ids);

            match ($request->action) {
                'delete'     => $query->delete(),
                'activate'   => $query->update(['is_active' => true]),
                'deactivate' => $query->update(['is_active' => false]),
            };

            DB::commit();
            $msgs = ['delete' => 'dihapus', 'activate' => 'diaktifkan', 'deactivate' => 'dinonaktifkan'];
            return back()->with('success', "$count pengguna berhasil " . $msgs[$request->action] . '.');

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('bulkAction error: ' . $e->getMessage());
            return back()->with('error', 'Gagal melakukan aksi massal.');
        }
    }
}
