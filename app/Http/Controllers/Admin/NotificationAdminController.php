<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\UserCentral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Daftar notifikasi yang sudah dikirim admin.
     */
    public function index(Request $request)
    {
        $query = Notification::with('sender')
            ->whereNotNull('pengirim_id')
            ->latest();

        // Filter by tipe penerima
        if ($request->filled('tipe_penerima')) {
            $query->where('tipe_penerima', $request->tipe_penerima);
        }

        // Filter by tipe notifikasi
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Statistik
        $stats = [
            'total'       => Notification::whereNotNull('pengirim_id')->count(),
            'semua'       => Notification::whereNotNull('pengirim_id')->where('tipe_penerima', 'semua')->count(),
            'guru'        => Notification::whereNotNull('pengirim_id')->where('tipe_penerima', 'guru')->count(),
            'siswa'       => Notification::whereNotNull('pengirim_id')->where('tipe_penerima', 'siswa')->count(),
            'user'        => Notification::whereNotNull('pengirim_id')->where('tipe_penerima', 'user')->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Form buat notifikasi baru.
     */
    public function create()
    {
        $gurus  = UserCentral::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();
        $siswas = UserCentral::where('role', 'siswa')->where('is_active', true)->orderBy('name')->get();

        return view('admin.notifications.create', compact('gurus', 'siswas'));
    }

    /**
     * Simpan & kirim notifikasi ke target.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'         => 'required|string|max:255',
            'pesan'         => 'required|string',
            'tipe'          => 'required|in:info,peringatan,sukses,error,pengumuman,sistem',
            'prioritas'     => 'required|in:rendah,sedang,tinggi,darurat',
            'tipe_penerima' => 'required|in:semua,guru,siswa,user',
            'penerima_ids'  => 'required_if:tipe_penerima,user|array',
            'penerima_ids.*'=> 'integer|exists:users_central,id',
            'url_aksi'      => 'nullable|url|max:500',
        ]);

        $senderId = Auth::id();
        $now      = now();
        $inserted = 0;

        if ($validated['tipe_penerima'] === 'user') {
            // Kirim ke user spesifik
            foreach ($validated['penerima_ids'] as $userId) {
                Notification::create([
                    'pengirim_id'   => $senderId,
                    'penerima_id'   => $userId,
                    'tipe_penerima' => 'user',
                    'judul'         => $validated['judul'],
                    'pesan'         => $validated['pesan'],
                    'tipe'          => $validated['tipe'],
                    'prioritas'     => $validated['prioritas'],
                    'url_aksi'      => $validated['url_aksi'] ?? null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
                $inserted++;
            }
        } elseif ($validated['tipe_penerima'] === 'guru') {
            // Kirim ke semua guru
            $guruIds = UserCentral::where('role', 'guru')->where('is_active', true)->pluck('id');
            foreach ($guruIds as $userId) {
                Notification::create([
                    'pengirim_id'   => $senderId,
                    'penerima_id'   => $userId,
                    'tipe_penerima' => 'guru',
                    'judul'         => $validated['judul'],
                    'pesan'         => $validated['pesan'],
                    'tipe'          => $validated['tipe'],
                    'prioritas'     => $validated['prioritas'],
                    'url_aksi'      => $validated['url_aksi'] ?? null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
                $inserted++;
            }
        } elseif ($validated['tipe_penerima'] === 'siswa') {
            // Kirim ke semua siswa
            $siswaIds = UserCentral::where('role', 'siswa')->where('is_active', true)->pluck('id');
            foreach ($siswaIds as $userId) {
                Notification::create([
                    'pengirim_id'   => $senderId,
                    'penerima_id'   => $userId,
                    'tipe_penerima' => 'siswa',
                    'judul'         => $validated['judul'],
                    'pesan'         => $validated['pesan'],
                    'tipe'          => $validated['tipe'],
                    'prioritas'     => $validated['prioritas'],
                    'url_aksi'      => $validated['url_aksi'] ?? null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
                $inserted++;
            }
        } else {
            // Kirim ke semua (guru + siswa) — 1 record dengan tipe_penerima='semua'
            Notification::create([
                'pengirim_id'   => $senderId,
                'penerima_id'   => null,
                'tipe_penerima' => 'semua',
                'judul'         => $validated['judul'],
                'pesan'         => $validated['pesan'],
                'tipe'          => $validated['tipe'],
                'prioritas'     => $validated['prioritas'],
                'url_aksi'      => $validated['url_aksi'] ?? null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
            $inserted = UserCentral::whereIn('role', ['guru', 'siswa'])->where('is_active', true)->count();
        }

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', "Notifikasi berhasil dikirim ke {$inserted} penerima.");
    }

    /**
     * Hapus notifikasi.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notifikasi berhasil dihapus.');
    }
}
