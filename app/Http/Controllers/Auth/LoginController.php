<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserCentral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $email    = $request->email;
        $password = $request->password;
        $remember = $request->boolean('remember');

        // ── Cari user langsung dari UserCentral model (bypass Auth facade) ──
        $user = UserCentral::where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
                ])->onlyInput('email');
            }

            // Login manual dengan Auth::login()
            Auth::login($user, $remember);
            $request->session()->regenerate();

            Log::info('Login berhasil', ['email' => $email, 'role' => $user->role]);

            return match($user->role) {
                'admin' => redirect()->intended(route('admin.dashboard')),
                'guru'  => redirect()->intended(route('guru.dashboard')),
                'siswa' => redirect()->intended(route('siswa.dashboard')),
                default => redirect('/'),
            };
        }

        // ── Fallback: cek tabel users lama ──────────────────────────────────
        $oldUser = \Illuminate\Support\Facades\DB::table('users')
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        if ($oldUser && Hash::check($password, $oldUser->password)) {
            // Buat/update akun di users_central
            $ucUser = UserCentral::withTrashed()->where('email', $email)->first();

            if (!$ucUser) {
                $ucUser = UserCentral::create([
                    'name'              => $oldUser->name,
                    'email'             => $oldUser->email,
                    'username'          => $oldUser->username ?? str_replace(' ', '_', strtolower($oldUser->name)),
                    'password'          => Hash::make($password),
                    'role'              => $oldUser->role ?? 'admin',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);
            } else {
                if ($ucUser->deleted_at) $ucUser->restore();
                $ucUser->update([
                    'password'  => Hash::make($password),
                    'is_active' => true,
                ]);
                $ucUser->refresh();
            }

            Auth::login($ucUser, $remember);
            $request->session()->regenerate();

            Log::info('Login via fallback users table, migrated', ['email' => $email]);

            return match($ucUser->role) {
                'admin' => redirect()->intended(route('admin.dashboard')),
                'guru'  => redirect()->intended(route('guru.dashboard')),
                'siswa' => redirect()->intended(route('siswa.dashboard')),
                default => redirect('/'),
            };
        }

        Log::warning('Login gagal', ['email' => $email]);

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
