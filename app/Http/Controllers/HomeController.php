<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Show welcome page for guests or redirect to dashboard.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function welcome()
    {
        // ✅ SIMPLE & SAFE: Redirect jika sudah login
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('welcome', [
            'stats' => [
                'siswa' => \App\Models\UserCentral::where('role', 'siswa')->count(),
                'guru'  => \App\Models\UserCentral::where('role', 'guru')->count(),
            ],
        ]);
    }

    /**
     * Redirect user to appropriate dashboard based on role.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToDashboard(): RedirectResponse
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'guru':
                return redirect()->route('guru.dashboard');
            case 'siswa':
                return redirect()->route('siswa.dashboard');
            default:
                Log::warning('User with invalid role attempted to access dashboard', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'ip' => request()->ip()
                ]);

                Auth::logout();
                return redirect('/')->with('error', 'Role tidak valid.');
        }
    }

    /**
     * Show about page.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('about', [
            'school_name' => config('app.name') ?: env('SCHOOL_NAME', 'SMK Kesehatan Trimurti Husada Ambon'),
            'address' => env('SCHOOL_ADDRESS', 'Jl. Tabea Jou No.8 Waihoka, Sirimau, Kota Ambon, Maluku'),
            'phone' => env('SCHOOL_PHONE', '(0910) 123456'),
            'email' => env('SCHOOL_EMAIL', 'info@smktrimurti.sch.id')
        ]);
    }

    /**
     * Show contact page.
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendContact(Request $request)
    {
        // Implement your contact form logic here
        return redirect()->back()->with('success', 'Pesan berhasil dikirim!');
    }

    /**
     * Show home page (for authenticated users).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function home()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return redirect()->route('welcome');
    }

    /**
     * Dashboard route handler - redirects to appropriate dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return $this->redirectToDashboard();
    }
}
