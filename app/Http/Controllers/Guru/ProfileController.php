<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Halaman edit profil.
     */
    public function edit(): View
    {
        // Query langsung dari DB — hindari cache Auth session
        $user        = \App\Models\UserCentral::find(Auth::id());
        $guruProfile = Guru::where('user_id', $user->id)->first();
        return view('guru.profile.edit', compact('user', 'guruProfile'));
    }

    /**
     * Simpan perubahan profil (akun + profil guru).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users_central,email,' . $user->id,
            'phone'    => 'nullable|string|max:20',
            'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'photo_url'=> 'nullable|url|max:500',
            // Profil guru
            'nip'                => 'nullable|string|max:50',
            'tempat_lahir'       => 'nullable|string|max:100',
            'tanggal_lahir'      => 'nullable|date',
            'jenis_kelamin'      => 'nullable|in:L,P',
            'alamat'             => 'nullable|string|max:500',
            'pendidikan_terakhir'=> 'nullable|string|max:255',
        ], [
            'email.unique' => 'Email sudah digunakan akun lain.',
            'foto.image'   => 'File harus berupa gambar.',
            'foto.mimes'   => 'Format foto: JPEG, PNG, JPG.',
            'foto.max'     => 'Ukuran foto maksimal 5MB.',
        ]);

        DB::beginTransaction();
        try {
            // ── Update users_central ──────────────────────────────────────────
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];

            // Handle upload foto ke users_central.photo
            if ($request->hasFile('foto')) {
                if ($user->photo && !str_starts_with($user->photo, 'http')) {
                    Storage::disk('public')->delete($user->photo);
                }
                $userData['photo'] = $request->file('foto')
                    ->store('profiles/guru', 'public');
            }

            // Handle URL foto dari Cloudinary
            if ($request->filled('photo_url') && str_starts_with($request->photo_url, 'http')) {
                $userData['photo'] = $request->photo_url;
                Log::info('Guru photo updated via Cloudinary', [
                    'user_id'   => $user->id,
                    'photo_url' => $request->photo_url,
                ]);
            }

            $user->update($userData);

            // ── Update / buat profil di tabel gurus ───────────────────────────
            $guruData = array_filter([
                'name'               => $request->name,
                'email'              => $request->email,
                'phone'              => $request->phone,
                'nip'                => $request->nip,
                'tempat_lahir'       => $request->tempat_lahir,
                'tanggal_lahir'      => $request->tanggal_lahir,
                'jenis_kelamin'      => $request->jenis_kelamin,
                'address'            => $request->alamat,
                'pendidikan_terakhir'=> $request->pendidikan_terakhir,
            ], fn($v) => $v !== null);

            Guru::updateOrCreate(
                ['user_id' => $user->id],
                $guruData
            );

            DB::commit();
            return redirect()->route('guru.profile.edit')
                ->with('success', 'Profil berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Guru profile update error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui profil: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update foto profil via Cloudinary URL — endpoint dedicated POST.
     * Lebih sederhana dan reliable daripada PUT dengan semua field.
     */
    public function updatePhotoUrl(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['photo_url' => 'required|url|max:500']);

        $user = Auth::user();

        // Pastikan URL bersih dari escape characters yang mungkin masuk
        $photoUrl = stripslashes($request->photo_url);
        $photoUrl = trim($photoUrl, '"\'');

        // Simpan langsung via query builder untuk menghindari transformasi model
        \Illuminate\Support\Facades\DB::table('users_central')
            ->where('id', $user->id)
            ->update(['photo' => $photoUrl, 'updated_at' => now()]);

        // Paksa session user refresh dari DB
        $freshUser = \App\Models\UserCentral::find($user->id);
        \Illuminate\Support\Facades\Auth::setUser($freshUser);

        Log::info('Guru photo_url updated', ['user_id' => $user->id, 'photo' => $photoUrl]);

        return response()->json(['success' => true, 'photo' => $photoUrl]);
    }

    /**
     * Update foto profil saja.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'foto.required' => 'Foto harus dipilih.',
            'foto.image'    => 'File harus berupa gambar.',
            'foto.mimes'    => 'Format foto: JPEG, PNG, JPG.',
            'foto.max'      => 'Ukuran foto maksimal 2MB.',
        ]);

        $user = Auth::user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $path = $request->file('foto')->store('profiles/guru', 'public');
        $user->update(['photo' => $path]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil.
     */
    public function removePhoto(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->update(['photo' => null]);
        }

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }

    /**
     * Halaman ubah password.
     */
    public function changePassword(): View
    {
        return view('guru.profile.change-password');
    }

    /**
     * Proses ubah password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password'      => 'required',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('guru.profile.edit')
            ->with('success', 'Password berhasil diubah.');
    }
}
