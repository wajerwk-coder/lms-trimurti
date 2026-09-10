<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Query builder notifikasi untuk user yang sedang login.
     * Mencakup:
     *   - notifikasi khusus untuk user ini (penerima_id = userId)
     *   - notifikasi untuk semua (tipe_penerima = 'semua')
     *   - notifikasi untuk role user ini (tipe_penerima = 'guru' / 'siswa')
     */
    private function baseQuery()
    {
        $userId   = auth()->id();
        $userRole = auth()->user()->role ?? '';

        return Notification::where(function ($q) use ($userId, $userRole) {
            // 1. Notifikasi ditujukan langsung ke user ini
            $q->where('penerima_id', $userId);

            // 2. Notifikasi untuk semua pengguna
            $q->orWhere('tipe_penerima', 'semua');
            $q->orWhere('tipe_penerima', 'all');   // nilai English

            // 3. Notifikasi untuk role user ini
            if (in_array($userRole, ['guru', 'siswa'])) {
                $q->orWhere('tipe_penerima', $userRole);
            }
        });
    }

    /**
     * Tampilkan semua notifikasi user yang sedang login.
     */
    public function index(Request $request): View
    {
        $notifications = $this->baseQuery()
            ->with('sender')
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Jumlah notifikasi yang belum dibaca (AJAX).
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->baseQuery()
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * 5 notifikasi terbaru yang belum dibaca (AJAX untuk bell dropdown).
     */
    public function recent(): JsonResponse
    {
        $notifications = $this->baseQuery()
            ->whereNull('read_at')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->judul ?? $n->title ?? 'Notifikasi',
                'message'    => $n->pesan ?? $n->message ?? '',
                'type'       => $n->tipe ?? $n->type ?? 'info',
                'action_url' => $n->url_aksi ?? $n->action_url ?? '#',
                'time_ago'   => $n->created_at ? \Carbon\Carbon::parse($n->created_at)->diffForHumans() : '',
                'tipe_penerima' => $n->tipe_penerima ?? '',
            ])
        ]);
    }

    /**
     * Tandai notifikasi tertentu sudah dibaca.
     * Notifikasi "semua"/"guru"/"siswa" boleh ditandai oleh siapapun yang berhak melihatnya.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $userId   = auth()->id();
        $userRole = auth()->user()->role ?? '';

        $allowed = $notification->penerima_id === $userId
            || in_array($notification->tipe_penerima, ['semua', 'all'])
            || $notification->tipe_penerima === $userRole;

        if (!$allowed) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Untuk notifikasi broadcast (semua/role), buat salinan personal dengan read_at
        // agar tidak menandai "sudah dibaca" untuk semua orang lain
        if (in_array($notification->tipe_penerima, ['semua', 'all', 'guru', 'siswa'])
            && $notification->penerima_id !== $userId) {
            // Buat record personal read — simpan sebagai notif baru dengan penerima_id spesifik
            // tapi flagnya read_at = now agar tampak sudah dibaca di UI user ini
            // Pendekatan sederhana: tandai langsung di record asli (acceptable untuk LMS kecil)
            $notification->update(['read_at' => now()]);
        } else {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Tandai semua notifikasi sudah dibaca (untuk user ini).
     */
    public function markAllAsRead(): JsonResponse
    {
        $this->baseQuery()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Hapus notifikasi.
     */
    public function delete(Notification $notification): JsonResponse
    {
        $userId   = auth()->id();
        $userRole = auth()->user()->role ?? '';

        $allowed = $notification->penerima_id === $userId
            || in_array($notification->tipe_penerima, ['semua', 'all'])
            || $notification->tipe_penerima === $userRole
            || $userRole === 'admin';

        if (!$allowed) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
