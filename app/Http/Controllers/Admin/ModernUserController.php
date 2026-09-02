<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\UserCentral;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ModernUserController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // ── Index pages ─────────────────────────────────────────────────────────

    public function index(): View
    {
        return view('admin.users.index-separated');
    }

    public function guruIndex(): View
    {
        $gurus = UserCentral::where('role', 'guru')
            ->with('guruProfile')
            ->latest()
            ->paginate(20);
        return view('admin.users.guru-index', compact('gurus'));
    }

    public function siswaIndex(): View
    {
        $siswas = UserCentral::where('role', 'siswa')
            ->with(['siswaProfile.kelas'])
            ->latest()
            ->paginate(20);
        return view('admin.users.siswa-index', compact('siswas'));
    }

    // ── Create forms ─────────────────────────────────────────────────────────

    public function createAdmin(): View
    {
        return view('admin.users.create-admin');
    }

    public function createGuru(): View
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.users.create-guru', compact('subjects'));
    }

    public function createSiswa(): View
    {
        $kelas    = Kelas::with('jurusan')->orderBy('name')->get();
        $jurusans = Jurusan::orderBy('name')->get();
        return view('admin.users.create-siswa', compact('kelas', 'jurusans'));
    }

    // ── Store ────────────────────────────────────────────────────────────────

    public function storeAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users_central,email',
            'username' => 'required|string|max:100|unique:users_central,username|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
        ], array_merge($this->messages(), [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, dan garis bawah.',
        ]));

        DB::beginTransaction();
        try {
            UserCentral::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'username'  => $request->username,
                'password'  => Hash::make($request->password),
                'role'      => 'admin',
                'phone'     => $request->phone,
                'is_active' => true,
            ]);

            // Tabel admins tidak ada di DB — skip profile creation
            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', 'Admin berhasil ditambahkan.');

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('storeAdmin error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menambahkan admin: ' . $e->getMessage())->withInput();
        }
    }

    public function storeGuru(Request $request): RedirectResponse
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|max:255|unique:users_central,email',
            'username'           => 'required|string|max:100|unique:users_central,username|regex:/^[a-zA-Z0-9_]+$/',
            'password'           => 'required|string|min:8|confirmed',
            'nip'                => 'required|string|max:50|unique:gurus,nip',
            'phone'              => 'nullable|string|max:20',
            'jenis_kelamin'      => 'nullable|in:L,P',
            'tempat_lahir'       => 'nullable|string|max:100',
            'tanggal_lahir'      => 'nullable|date',
            'alamat'             => 'nullable|string|max:500',
            'email_pribadi'      => 'nullable|email|max:255',
            'subject_ids'        => 'required|array|min:1',
            'subject_ids.*'      => 'exists:subjects,id',
            'pendidikan_terakhir'=> 'nullable|in:D3,S1,S2,S3',
            'jurusan_pendidikan' => 'nullable|string|max:255',
            'tahun_mulai_kerja'  => 'nullable|integer|min:1970|max:' . date('Y'),
        ], array_merge($this->messages(), [
            'username.regex'       => 'Username hanya boleh berisi huruf, angka, dan garis bawah (_).',
            'nip.unique'           => 'NIP sudah terdaftar di sistem.',
            'subject_ids.required' => 'Pilih minimal satu mata pelajaran.',
            'subject_ids.min'      => 'Pilih minimal satu mata pelajaran.',
        ]));

        DB::beginTransaction();
        try {
            // 1. Simpan akun login di users_central
            $user = UserCentral::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'username'  => $request->username,
                'password'  => Hash::make($request->password),
                'role'      => 'guru',
                'phone'     => $request->phone,
                'is_active' => true,
            ]);

            // 2. Nama mapel untuk kolom mata_pelajaran (comma-separated string)
            $subjectNames = Subject::whereIn('id', $request->subject_ids)
                ->orderBy('name')
                ->pluck('name')
                ->implode(', ');

            $guruData = [
                'user_id'             => $user->id,
                'nip'                 => $request->nip,
                'name'                => $request->name,
                'email'               => $request->email,
                'mata_pelajaran'      => $subjectNames,
                'pendidikan_terakhir' => $request->pendidikan_terakhir ?? 'S1',
                'status'              => 'aktif',
                'is_active'           => true,
                'jenis_kelamin'       => $request->jenis_kelamin   ?: null,
                'tempat_lahir'        => $request->tempat_lahir    ?: null,
                'tanggal_lahir'       => $request->tanggal_lahir   ?: null,
                'address'             => $request->alamat          ?: null,
                'phone'               => $request->phone           ?: null,
            ];

            $extrasIfExist = [
                'email_pribadi'      => $request->email_pribadi,
                'jurusan_pendidikan' => $request->jurusan_pendidikan,
                'tahun_mulai_kerja'  => $request->tahun_mulai_kerja ?: null,
            ];

            foreach ($extrasIfExist as $col => $val) {
                if ($val !== null && $val !== '' && \Illuminate\Support\Facades\Schema::hasColumn('gurus', $col)) {
                    $guruData[$col] = $val;
                }
            }

            // 3. Simpan profil guru
            $guru = Guru::create($guruData);

            // 4. Sync pivot guru_subjects
            $guru->subjects()->sync($request->subject_ids);

            DB::commit();
            return redirect()->route('admin.users.guru')
                ->with('success', 'Guru ' . $request->name . ' berhasil ditambahkan.');

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('storeGuru error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return back()
                ->with('error', 'Gagal menambahkan guru: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function storeSiswa(Request $request): RedirectResponse
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users_central,email',
            'username'        => 'required|string|max:100|unique:users_central,username|regex:/^[a-zA-Z0-9_]+$/',
            'password'        => 'required|string|min:8|confirmed',
            'nis'             => 'required|string|max:20|unique:siswa,nis',
            'nisn'            => 'required|string|max:20|unique:siswa,nisn',
            'phone'           => 'nullable|string|max:20',
            'jenis_kelamin'   => 'nullable|in:L,P',
            'tempat_lahir'    => 'nullable|string|max:100',
            'tanggal_lahir'   => 'nullable|date',
            'alamat'          => 'nullable|string|max:500',
            'kelas_id'        => 'required|exists:classes,id',
            'major'           => 'required|string|max:100',
            'tahun_ajaran'    => 'required|string|max:20',
            'nama_ortu'       => 'nullable|string|max:100',
            'no_telepon_ortu' => 'nullable|string|max:20',
            'golongan_darah'  => 'nullable|in:A,B,AB,O',
            'riwayat_penyakit'=> 'nullable|string|max:500',
            'alergi'          => 'nullable|string|max:500',
            'info_kesehatan'  => 'nullable|string|max:1000',
        ], array_merge($this->messages(), [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, dan garis bawah (_).',
            'nis.unique'     => 'NIS sudah terdaftar di sistem.',
            'nisn.unique'    => 'NISN sudah terdaftar di sistem.',
        ]));

        DB::beginTransaction();
        try {
            // 1. Buat akun di users_central
            $user = UserCentral::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'username'  => $request->username,
                'password'  => Hash::make($request->password),
                'role'      => 'siswa',
                'phone'     => $request->phone,
                'is_active' => true,
            ]);

            // 2. Buat profil di tabel siswa
            // Kolom siswa: user_id, nis, nisn, jenis_kelamin, tempat_lahir,
            //              tanggal_lahir, alamat, no_telepon, kelas_id, major,
            //              tahun_ajaran, nama_ortu, no_telepon_ortu,
            //              golongan_darah, riwayat_penyakit, alergi,
            //              info_kesehatan, foto, status
            Siswa::create([
                'user_id'          => $user->id,
                'nis'              => $request->nis,
                'nisn'             => $request->nisn,
                'jenis_kelamin'    => $request->jenis_kelamin    ?? 'L',
                'tempat_lahir'     => $request->tempat_lahir     ?? '-',
                'tanggal_lahir'    => $request->tanggal_lahir    ?? now()->format('Y-m-d'),
                'alamat'           => $request->alamat           ?: '-',
                'no_telepon'       => $request->phone            ?: '-',
                'kelas_id'         => $request->kelas_id,
                'major'            => $request->major,
                'tahun_ajaran'     => $request->tahun_ajaran,
                'nama_ortu'        => $request->nama_ortu        ?: null,
                'no_telepon_ortu'  => $request->no_telepon_ortu  ?: null,
                'golongan_darah'   => $request->golongan_darah   ?: null,
                'riwayat_penyakit' => $request->riwayat_penyakit ?: null,
                'alergi'           => $request->alergi           ?: null,
                'info_kesehatan'   => $request->info_kesehatan   ?: null,
                'status'           => 'aktif',
            ]);

            DB::commit();
            return redirect()->route('admin.users.siswa')
                ->with('success', 'Siswa ' . $request->name . ' berhasil ditambahkan.');

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('storeSiswa error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menambahkan siswa: ' . $e->getMessage())->withInput();
        }
    }

    // ── Edit & Update ─────────────────────────────────────────────────────────

    public function edit($id): View
    {
        $user = UserCentral::findOrFail($id);

        return match ($user->role) {
            'guru'  => view('admin.users.edit-guru', [
                            'user'     => $user,
                            'profile'  => $user->guruProfile?->load('subjects'),
                            'subjects' => Subject::orderBy('name')->get(),
                            'selectedSubjectIds' => $user->guruProfile?->subjects->pluck('id')->toArray() ?? [],
                       ]),
            'siswa' => view('admin.users.edit-siswa', [
                            'user'     => $user,
                            'kelas'    => Kelas::orderBy('name')->get(),
                            'jurusans' => Jurusan::orderBy('name')->get(),
                       ]),
            default => view('admin.users.edit-admin', compact('user')),
        };
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $user = UserCentral::findOrFail($id);

        return match ($user->role) {
            'guru'  => $this->updateGuru($request, $user),
            'siswa' => $this->updateSiswa($request, $user),
            default => $this->updateAdmin($request, $user),
        };
    }

    public function destroy($id): RedirectResponse
    {
        $user = UserCentral::findOrFail($id);
        $role = $user->role;

        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();

            return match ($role) {
                'guru'  => redirect()->route('admin.users.guru')->with('success', 'Guru berhasil dihapus.'),
                'siswa' => redirect()->route('admin.users.siswa')->with('success', 'Siswa berhasil dihapus.'),
                default => redirect()->route('admin.users.index')->with('success', 'Admin berhasil dihapus.'),
            };
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('destroy error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus pengguna.');
        }
    }

    // ── Private update helpers ─────────────────────────────────────────────

    private function updateAdmin(Request $request, UserCentral $user): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users_central,email,' . $user->id,
            'username' => 'required|string|unique:users_central,username,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ], $this->messages());

        $data = [
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'phone'    => $request->phone,
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Admin berhasil diperbarui.');
    }

    private function updateGuru(Request $request, UserCentral $user): RedirectResponse
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users_central,email,' . $user->id,
            'username'           => 'required|string|unique:users_central,username,' . $user->id,
            'password'           => 'nullable|string|min:8|confirmed',
            'nip'                => 'required|string|max:50|unique:gurus,nip,' . ($user->guruProfile?->id ?? 0),
            'subject_ids'        => 'nullable|array',
            'subject_ids.*'      => 'exists:subjects,id',
            'jenis_kelamin'      => 'nullable|in:L,P',
            'pendidikan_terakhir'=> 'nullable|in:D3,S1,S2,S3',
            'tahun_mulai_kerja'  => 'nullable|integer|min:1970|max:' . date('Y'),
        ], $this->messages());

        DB::beginTransaction();
        try {
            // Update akun di users_central
            $userData = [
                'name'     => $request->name,
                'email'    => $request->email,
                'username' => $request->username,
                'phone'    => $request->phone,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);

            // Nama mapel dari subject_ids baru (atau tetap yang lama)
            $subjectNames = null;
            if ($request->has('subject_ids') && !empty($request->subject_ids)) {
                $subjectNames = Subject::whereIn('id', $request->subject_ids)
                    ->orderBy('name')
                    ->pluck('name')
                    ->implode(', ');
            }

            $guruData = [
                'nip'            => $request->nip,
                'mata_pelajaran' => $subjectNames ?? $user->guruProfile?->mata_pelajaran,
                'name'           => $request->name,
                'email'          => $request->email,
                'address'        => $request->alamat          ?: null,
                'phone'          => $request->phone           ?: null,
                // Selalu update field ini — bukan hanya jika filled()
                'jenis_kelamin'      => $request->jenis_kelamin      ?: null,
                'tempat_lahir'       => $request->tempat_lahir       ?: null,
                'tanggal_lahir'      => $request->tanggal_lahir      ?: null,
                'pendidikan_terakhir'=> $request->pendidikan_terakhir ?: $user->guruProfile?->pendidikan_terakhir,
            ];

            if ($request->filled('email_pribadi'))      $guruData['email_pribadi']       = $request->email_pribadi;
            if ($request->filled('jurusan_pendidikan')) $guruData['jurusan_pendidikan']  = $request->jurusan_pendidikan;
            if ($request->filled('tahun_mulai_kerja'))  $guruData['tahun_mulai_kerja']   = $request->tahun_mulai_kerja;

            if ($user->guruProfile) {
                $user->guruProfile->update($guruData);
                // Sync pivot mata pelajaran
                if ($request->has('subject_ids')) {
                    $user->guruProfile->subjects()->sync($request->subject_ids ?? []);
                }
            } else {
                $guru = Guru::create(array_merge([
                    'user_id'  => $user->id,
                    'status'   => 'aktif',
                    'is_active'=> true,
                    'name'     => $request->name,
                    'email'    => $request->email,
                ], $guruData));
                if ($request->has('subject_ids')) {
                    $guru->subjects()->sync($request->subject_ids ?? []);
                }
            }

            DB::commit();
            return redirect()->route('admin.users.guru')
                ->with('success', 'Data guru ' . $request->name . ' berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('updateGuru error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui guru: ' . $e->getMessage())->withInput();
        }
    }

    private function updateSiswa(Request $request, UserCentral $user): RedirectResponse
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users_central,email,' . $user->id,
            'username'        => 'required|string|unique:users_central,username,' . $user->id,
            'password'        => 'nullable|string|min:8|confirmed',
            'nis'             => 'required|string|max:20|unique:siswa,nis,' . ($user->siswaProfile?->id ?? 0),
            'nisn'            => 'required|string|max:20|unique:siswa,nisn,' . ($user->siswaProfile?->id ?? 0),
            'kelas_id'        => 'required|exists:classes,id',
            'major'           => 'required|string|max:100',
            'jenis_kelamin'   => 'nullable|in:L,P',
            'golongan_darah'  => 'nullable|in:A,B,AB,O',
            'tahun_ajaran'    => 'nullable|string|max:20',
        ], $this->messages());

        DB::beginTransaction();
        try {
            // Update akun
            $userData = [
                'name'     => $request->name,
                'email'    => $request->email,
                'username' => $request->username,
                'phone'    => $request->phone,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);

            // Update atau buat profil siswa — JANGAN pakai array_filter
            // agar field yang dikosongkan (misal: tanggal_lahir) tetap di-update ke null
            $siswaData = [
                'nis'              => $request->nis,
                'nisn'             => $request->nisn,
                'jenis_kelamin'    => $request->jenis_kelamin    ?: null,
                'tempat_lahir'     => $request->tempat_lahir     ?: null,
                'tanggal_lahir'    => $request->tanggal_lahir    ?: null,
                'alamat'           => $request->alamat           ?: null,
                'no_telepon'       => $request->phone            ?: null,
                'kelas_id'         => $request->kelas_id,
                'major'            => $request->major,
                'tahun_ajaran'     => $request->tahun_ajaran     ?: null,
                'nama_ortu'        => $request->nama_ortu        ?: null,
                'no_telepon_ortu'  => $request->no_telepon_ortu  ?: null,
                'golongan_darah'   => $request->golongan_darah   ?: null,
                'riwayat_penyakit' => $request->riwayat_penyakit ?: null,
                'alergi'           => $request->alergi           ?: null,
                'info_kesehatan'   => $request->info_kesehatan   ?: null,
            ];

            if ($user->siswaProfile) {
                $user->siswaProfile->update($siswaData);
            } else {
                Siswa::create(array_merge([
                    'user_id'       => $user->id,
                    'status'        => 'aktif',
                    // Kolom NOT NULL — beri default jika kosong
                    'jenis_kelamin' => $request->jenis_kelamin    ?? 'L',
                    'tempat_lahir'  => $request->tempat_lahir     ?? '-',
                    'tanggal_lahir' => $request->tanggal_lahir    ?? now()->format('Y-m-d'),
                    'alamat'        => $request->alamat           ?: '-',
                    'no_telepon'    => $request->phone            ?: '-',
                ], $siswaData));
            }

            DB::commit();
            return redirect()->route('admin.users.siswa')
                ->with('success', 'Data siswa ' . $request->name . ' berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('updateSiswa error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui siswa: ' . $e->getMessage())->withInput();
        }
    }

    private function messages(): array
    {
        return [
            'name.required'     => 'Nama wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar di sistem.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan, coba yang lain.',
            'username.regex'    => 'Username hanya boleh berisi huruf, angka, dan garis bawah (_).',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'nip.required'      => 'NIP wajib diisi.',
            'nip.unique'        => 'NIP sudah terdaftar.',
            'nis.required'      => 'NIS wajib diisi.',
            'nis.unique'        => 'NIS sudah terdaftar.',
            'nisn.required'     => 'NISN wajib diisi.',
            'nisn.unique'       => 'NISN sudah terdaftar.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists'   => 'Kelas tidak ditemukan.',
            'major.required'    => 'Jurusan wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
        ];
    }
}
