<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Menampilkan form permintaan link reset password.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Mengirim link reset password (Web).
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users_central,email',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'Email tidak terdaftar dalam sistem',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Log activity
        Log::info('Link reset password diminta', [
            'email' => $request->email,
            'status' => $status,
            'ip' => $request->ip()
        ]);

        // Selalu tampilkan pesan sukses untuk keamanan
        return redirect()->back()->with('status', 'Jika email Anda terdaftar, kami telah mengirim link reset password.');
    }

    /**
     * Mengirim link reset password (API).
     */
    public function apiSendResetLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users_central,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Log activity
        Log::info('API Link reset password diminta', [
            'email' => $request->email,
            'status' => $status,
            'ip' => $request->ip()
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Jika email Anda terdaftar, kami telah mengirim link reset password.',
                'data' => ['status' => __($status)]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal memproses permintaan. Silakan coba lagi nanti.',
            'error' => config('app.debug') ? __($status) : null
        ], 400);
    }
}
