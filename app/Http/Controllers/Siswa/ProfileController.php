<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\UserCentral;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'siswa']);
    }

    /**
     * Display the student profile.
     */
    public function index()
    {
        /** @var \App\Models\UserCentral $user */
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        /** @var \App\Models\Siswa $student */
        $student = Siswa::with('kelas')->where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        return view('siswa.profile.index', compact('user', 'student'));
    }

    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        // fresh() memaksa reload dari DB, bukan dari cache sesi
        $user = Auth::user()->fresh();

        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $student = Siswa::with('kelas')->where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        return view('siswa.profile.edit', compact('user', 'student'));
    }

    /**
     * Update foto via Cloudinary URL saja — endpoint POST dedicated.
     */
    public function updatePhotoUrl(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['photo_url' => 'required|url|max:500']);
        $user = Auth::user();
        $user->photo = $request->photo_url;
        $user->save();
        // Paksa refresh auth user dari DB agar sesi terupdate
        Auth::setUser($user->fresh());
        \Illuminate\Support\Facades\Log::info('Siswa photo_url updated', ['user_id' => $user->id]);
        return response()->json(['success' => true, 'photo' => $request->photo_url]);
    }

    /**
     * Update the student profile.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        /** @var \\App\\Models\\Siswa $student */
        $student = Siswa::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users_central,email,' . $user->id,
            'nisn'            => 'required|string|unique:siswa,nisn,' . $student->id,
            'jenis_kelamin'   => 'nullable|in:L,P',
            'tanggal_lahir'   => 'nullable|date',
            'alamat'          => 'nullable|string|max:500',
            'no_hp'           => 'nullable|string|max:20',
            'nama_ortu'       => 'nullable|string|max:255',
            'no_telepon_ortu' => 'nullable|string|max:20',
            'foto'            => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'photo_url'       => 'nullable|url|max:500',
            'current_password'=> 'required_with:password',
            'password'        => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return redirect()->back()
                        ->with('error', 'Password saat ini tidak sesuai')
                        ->withInput();
                }
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Handle foto upload for student profile
            $studentData = [
                'nisn' => $request->nisn,
            ];

            if ($request->hasFile('foto')) {
                // Delete old photo if local path
                if ($student->foto && !str_starts_with($student->foto, 'http')) {
                    Storage::disk('public')->delete($student->foto);
                }

                $foto = $request->file('foto');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $foto->getClientOriginalName());
                $path = $foto->storeAs('student_photos', $filename, 'public');
                $studentData['foto'] = 'student_photos/' . $filename;
            }

            // Handle URL foto dari Cloudinary (di users_central.photo)
            if ($request->filled('photo_url') && str_starts_with($request->photo_url, 'http')) {
                $userData['photo'] = $request->photo_url;
            }

            if ($request->filled('jenis_kelamin')) {
                $studentData['jenis_kelamin'] = $request->jenis_kelamin;
            }

            if ($request->filled('tanggal_lahir')) {
                $studentData['tanggal_lahir'] = $request->tanggal_lahir;
            }

            if ($request->filled('tempat_lahir')) {
                $studentData['tempat_lahir'] = $request->tempat_lahir;
            }

            if ($request->filled('alamat')) {
                $studentData['alamat'] = $request->alamat;
            }

            if ($request->filled('no_hp')) {
                $studentData['no_telepon'] = $request->no_hp;
            }

            if ($request->filled('nama_ortu')) {
                $studentData['nama_ortu'] = $request->nama_ortu;
            }

            if ($request->filled('no_telepon_ortu')) {
                $studentData['no_telepon_ortu'] = $request->no_telepon_ortu;
            }

            $student->update($studentData);

            Log::info('Student profile updated', [
                'user_id' => $user->id,
                'student_id' => $student->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('siswa.profile.edit')
                ->with('success', 'Profil berhasil diperbarui!');

        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui profil: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update student password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Password saat ini tidak sesuai');
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]); // ✅ IDE sekarang mengenali method ini

        Log::info('Student password updated', [
            'user_id' => $user->id,
            'ip' => $request->ip()
        ]);

        return redirect()->route('siswa.profile.index')
            ->with('success', 'Password berhasil diubah!');
    }

    /**
     * Display medical information.
     */
    public function medicalInfo()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        /** @var \\App\\Models\\Siswa $student */
        $student = Siswa::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        return view('siswa.profile.medical', compact('user', 'student'));
    }

    /**
     * Update medical information.
     */
    public function updateMedicalInfo(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        /** @var \\App\\Models\\Siswa $student */
        $student = Siswa::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'riwayat_penyakit' => 'nullable|string|max:1000',
            'alergi' => 'nullable|string|max:500',
            'info_kesehatan' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student->update([
            'golongan_darah' => $request->golongan_darah,
            'riwayat_penyakit' => $request->riwayat_penyakit,
            'alergi' => $request->alergi,
            'info_kesehatan' => $request->info_kesehatan,
        ]); // ✅ IDE sekarang mengenali method ini

        Log::info('Student medical info updated', [
            'student_id' => $student->id,
            'user_id' => $user->id,
            'ip' => $request->ip()
        ]);

        return redirect()->route('siswa.profile.medical')
            ->with('success', 'Informasi kesehatan berhasil diperbarui!');
    }
}
