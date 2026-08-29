<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserCentral;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Menampilkan form registrasi.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Menangani pendaftaran user baru.
     */
    public function register(Request $request)
    {
        // 🔒 Batasi pendaftaran admin hanya di environment local
        if ($request->role === 'admin' && !app()->isLocal()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pendaftaran sebagai admin tidak diizinkan.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\.]+$/',
            'email' => 'required|string|email|max:255|unique:users_central,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:siswa,guru,admin',
            'terms' => 'accepted',
        ], [
            'name.required' => 'Nama lengkap harus diisi',
            'name.regex' => 'Nama hanya boleh mengandung huruf, spasi, dan titik',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar dalam sistem',
            'password.required' => 'Password harus diisi',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
            'role.required' => 'Role harus dipilih',
            'role.in' => 'Role tidak valid',
            'terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Terdapat kesalahan dalam pengisian form. Silakan periksa kembali.');
        }

        try {
            DB::beginTransaction();

            $user = UserCentral::create([
                'name' => ucwords(strtolower(trim($request->name))),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'active',
                'email_verified_at' => now(), // Hanya untuk development
            ]);

            // ✅ DIPERBAIKI: Buat data profil berdasarkan role
            if ($request->role === 'siswa') {
                Siswa::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => 'aktif'
                ]);
            } elseif ($request->role === 'guru') {
                Guru::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => 'active'
                ]);
            }

            DB::commit();

            Auth::login($user);

            Log::info('User baru berhasil didaftarkan', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'ip' => $request->ip()
            ]);

            return $this->redirectBasedOnRole($user);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal mendaftarkan user: ' . $e->getMessage(), [
                'email' => $request->email ?? 'N/A',
                'role' => $request->role ?? 'N/A',
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi dalam beberapa saat.');
        }
    }

    /**
     * Redirect user berdasarkan role setelah registrasi.
     */
    protected function redirectBasedOnRole($user)
    {
        $routes = [
            'guru' => 'guru.dashboard',
            'siswa' => 'siswa.dashboard',
            'admin' => 'admin.dashboard',
        ];

        $messages = [
            'guru' => 'Selamat datang! Akun guru berhasil dibuat.',
            'siswa' => 'Selamat datang! Akun siswa berhasil dibuat.',
            'admin' => 'Selamat datang! Akun admin berhasil dibuat.',
        ];

        $route = $routes[$user->role] ?? '/';
        $message = $messages[$user->role] ?? 'Selamat datang! Akun berhasil dibuat.';

        return redirect()->route($route)
            ->with('success', $message);
    }
}