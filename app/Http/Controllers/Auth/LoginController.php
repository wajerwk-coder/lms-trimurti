<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        // ── Coba login via UserCentral (users_central) ──────────────────────
        // Pastikan provider ke users_central saat runtime
        config([
            'auth.guards.web.provider'     => 'users_central',
            'auth.providers.users_central' => [
                'driver' => 'eloquent',
                'model'  => \App\Models\UserCentral::class,
            ],
        ]);

        // Re-bind user resolver agar Guard pakai UserCentral
        Auth::extend('session', function ($app, $name, array $config) {
            return new \Illuminate\Auth\SessionGuard(
                $name,
                Auth::createUserProvider('users_central'),
                $app['session.store'],
                $app['request']
            );
        });

        // Attempt login
        if (Auth::guard('web')->attempt(['email' => $email, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda tidak aktif.'])->onlyInput('email');
            }

            Log::info('Login berhasil via users_central', ['email' => $email, 'role' => $user->role]);

            return match($user->role) {
                'admin' => redirect()->intended(route('admin.dashboard')),
                'guru'  => redirect()->intended(route('guru.dashboard')),
                'siswa' => redirect()->intended(route('siswa.dashboard')),
                default => redirect('/'),
            };
        }

        // ── Fallback: coba tabel users lama ─────────────────────────────────
        $oldUser = \Illuminate\Support\Facades\DB::table('users')
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        if ($oldUser && Hash::check($password, $oldUser->password)) {
            // User ada di tabel lama — cari/buat di users_central
            $ucUser = \App\Models\UserCentral::withTrashed()->where('email', $email)->first();

            if (!$ucUser) {
                // Buat di users_central
                $ucUser = \App\Models\UserCentral::create([
                    'name'               => $oldUser->name,
                    'email'              => $oldUser->email,
                    'username'           => $oldUser->username ?? strtolower(str_replace(' ', '.', $oldUser->name)),
                    'password'           => Hash::make($password),
                    'role'               => $oldUser->role ?? 'admin',
                    'is_active'          => true,
                    'email_verified_at'  => now(),
                ]);
                Log::info('User migrated dari users ke users_central', ['email' => $email]);
            } elseif ($ucUser->deleted_at) {
                $ucUser->restore();
                $ucUser->update(['password' => Hash::make($password), 'is_active' => true]);
            }

            Auth::login($ucUser, $remember);
            $request->session()->regenerate();

            Log::info('Login via fallback users table', ['email' => $email, 'role' => $ucUser->role]);

            return match($ucUser->role) {
                'admin' => redirect()->intended(route('admin.dashboard')),
                'guru'  => redirect()->intended(route('guru.dashboard')),
                'siswa' => redirect()->intended(route('siswa.dashboard')),
                default => redirect('/'),
            };
        }

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
