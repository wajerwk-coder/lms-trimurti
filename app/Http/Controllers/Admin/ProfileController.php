<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function edit()
    {
        // fresh() memaksa reload dari DB, bukan dari cache sesi
        $user = Auth::user()->fresh();
        return view('admin.profile.edit', compact('user'));
    }

    public function updatePhotoUrl(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['photo_url' => 'required|url|max:500']);
        $user = Auth::user();
        $photoUrl = stripslashes($request->photo_url);
        $photoUrl = trim($photoUrl, '"\'');
        \Illuminate\Support\Facades\DB::table('users_central')
            ->where('id', $user->id)
            ->update(['photo' => $photoUrl, 'updated_at' => now()]);
        \Illuminate\Support\Facades\Log::info('Admin photo_url updated', ['user_id' => $user->id, 'photo' => $photoUrl]);
        return response()->json(['success' => true, 'photo' => $photoUrl]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        try {
            $data = [
                'name'  => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? $user->phone,
            ];

            // Upload foto file (lokal) — hanya untuk development
            if ($request->hasFile('photo')) {
                if (!empty($user->photo) && \Storage::disk('public')->exists($user->photo)) {
                    \Storage::disk('public')->delete($user->photo);
                }
                $data['photo'] = $request->file('photo')
                    ->store('profiles/admin', 'public');
            }

            // URL foto dari external (imgbb, dll) — untuk production/Railway
            if ($request->filled('photo_url')) {
                $data['photo'] = $request->photo_url;
            }

            // Ubah password jika diisi
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return redirect()->route('admin.profile.edit')
                ->with('success', 'Profil berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Admin Profile Update: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
}
