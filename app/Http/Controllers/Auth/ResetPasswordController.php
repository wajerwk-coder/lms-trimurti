<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ResetPasswordController extends Controller
{
    /**
     * Menampilkan form reset password.
     */
    public function showResetForm(Request $request)
    {
        return view('auth.passwords.reset', [
            'token' => $request->route('token'),
            'email' => $request->email
        ]);
    }

    /**
     * Menangani permintaan reset password.
     */
    public function reset(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users_central,email',
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'Email tidak terdaftar dalam sistem',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.letters' => 'Password harus mengandung huruf',
            'password.mixedCase' => 'Password harus mengandung huruf besar dan kecil',
            'password.numbers' => 'Password harus mengandung angka',
            'password.symbols' => 'Password harus mengandung simbol',
            'password.uncompromised' => 'Password ini tidak aman karena pernah bocor di internet. Silakan gunakan password lain.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Log::info('Password berhasil direset', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
        }

        // Log failed attempt
        Log::warning('Reset password gagal', [
            'email' => $request->email,
            'status' => $status,
            'ip' => $request->ip()
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Link reset password tidak valid atau sudah kadaluarsa. Silakan minta link reset baru.'
            ]);
    }
}
