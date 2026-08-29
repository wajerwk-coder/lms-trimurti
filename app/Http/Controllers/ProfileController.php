<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for editing the profile.
     */
    public function edit(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $role = $user->role;

        $additionalData = null;

        if ($role === 'siswa') {
            $additionalData = Siswa::where('user_id', $user->id)->first();
        } elseif ($role === 'guru') {
            $additionalData = Guru::where('user_id', $user->id)->first();
        }

        return view('profile.edit', compact('user', 'role', 'additionalData'));
    }

    /**
     * Update the user profile.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $role = $user->role;

        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users_central,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'current_password' => 'required_with:password',
            'password' => 'nullable|min:6|confirmed'
        ];

        if ($role === 'siswa') {
            $siswa = Siswa::where('user_id', $user->id)->first();
            $siswaId = $siswa ? $siswa->id : 0;

            $validationRules = array_merge($validationRules, [
                'nis' => 'required|string|unique:siswa,nis,' . $siswaId,
                'jenis_kelamin' => 'required|in:L,P',
                'tanggal_lahir' => 'required|date',
                'alamat' => 'nullable|string|max:500',
                'nama_ortu' => 'nullable|string|max:255',
                'no_telepon_ortu' => 'nullable|string|max:15',
            ]);
        } elseif ($role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            $guruId = $guru ? $guru->id : 0;

            $validationRules = array_merge($validationRules, [
                'nip' => 'required|string|unique:gurus,nip,' . $guruId,
                'jenis_kelamin' => 'required|in:L,P',
                'tanggal_lahir' => 'required|date',
                'alamat' => 'nullable|string|max:500',
                'bidang_keahlian' => 'nullable|string|max:255',
            ]);
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];

            if ($request->filled('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', 'Password saat ini tidak sesuai')
                        ->withInput();
                }
                $userData['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('photo')) {
                if ($user->photo) {
                    Storage::disk('public')->delete($user->photo);
                }

                $photo = $request->file('photo');
                $filename = time() . '_' . $photo->getClientOriginalName();
                $path = $photo->storeAs('profile_photos', $filename, 'public');
                $userData['photo'] = $path;
            }

            $user->update($userData);

            if ($role === 'siswa') {
                $siswaData = [
                    'nis' => $request->nis,
                    'name' => $request->name,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tanggal_lahir' => $request->date_lahir,
                    'alamat' => $request->alamat,
                    'nama_ortu' => $request->nama_ortu,
                    'no_telepon_ortu' => $request->no_telepon_ortu,
                ];

                $siswa = Siswa::where('user_id', $user->id)->first();
                if ($siswa) {
                    $siswa->update($siswaData);
                } else {
                    $siswaData['user_id'] = $user->id;
                    Siswa::create($siswaData);
                }

            } elseif ($role === 'guru') {
                $guruData = [
                    'nip' => $request->nip,
                    'name' => $request->name,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tanggal_lahir' => $request->date_lahir,
                    'alamat' => $request->alamat,
                    'bidang_keahlian' => $request->bidang_keahlian,
                ];

                $guru = Guru::where('user_id', $user->id)->first();
                if ($guru) {
                    $guru->update($guruData);
                } else {
                    $guruData['user_id'] = $user->id;
                    Guru::create($guruData);
                }
            }

            DB::commit();

            Log::info('Profile updated successfully', [
                'user_id' => $user->id,
                'role' => $role,
                'ip' => $request->ip()
            ]);

            return redirect()->route('profile.edit')
                ->with('success', 'Profil berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
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
     * Display medical information (students only).
     */
    public function medicalInfo(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            abort(403, 'Fitur ini hanya untuk siswa');
        }

        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            // Jika data siswa tidak ditemukan, tetap kembalikan view dengan pesan error
            return view('profile.medical', [
                'user' => $user,
                'siswa' => null
            ])->with('error', 'Data siswa tidak ditemukan');
        }

        return view('profile.medical', compact('user', 'siswa'));
    }

    /**
     * Update medical information.
     */
    public function updateMedicalInfo(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            abort(403, 'Fitur ini hanya untuk siswa');
        }

        /** @var \App\Models\Siswa $siswa */
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return redirect()->route('profile.edit')
                ->with('error', 'Data siswa tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'riwayat_penyakit' => 'nullable|string|max:1000',
            'alergi' => 'nullable|string|max:500',
            'catatan_kesehatan' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $siswa->update([
                'golongan_darah' => $request->golongan_darah,
                'riwayat_penyakit' => $request->riwayat_penyakit,
                'alergi' => $request->alergi,
                'catatan_kesehatan' => $request->catatan_kesehatan,
            ]);

            DB::commit();

            Log::info('Medical info updated successfully', [
                'siswa_id' => $siswa->id,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('profile.medical')
                ->with('success', 'Informasi kesehatan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Medical info update failed: ' . $e->getMessage(), [
                'siswa_id' => $siswa->id,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);
            return redirect()->back()
                ->with('error', 'Gagal memperbarui informasi kesehatan: ' . $e->getMessage())
                ->withInput();
        }
    }
}