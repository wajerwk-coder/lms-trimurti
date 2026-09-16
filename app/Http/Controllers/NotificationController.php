<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Helper: apakah user ini sudah membaca notifikasi ini ────────────────
    private function isRead(int $userId, int $notificationId): bool
    {
        return NotificationRead::where('user_id', $userId)
            ->where('notification_id', $notificationId)
            ->exists();
    }

    // ── Helper: tandai satu notifikasi sudah dibaca oleh user ini ───────────
    private function markRead(int $userId, int $notificationId): void
    {
        NotificationRead::firstOrCreate(
            ['user_id' => $userId, 'notification_id' => $notificationId],
            ['read_at' => now()]
        );
    }

    /**
     * Query builder notifikasi yang relevan untuk user yang sedang login.
     * Mencakup: notifikasi personal + broadcast ke semua/role.
     */
    private function baseQuery()
    {
        $userId   = auth()->id();
        $userRole = auth()->user()->role ?? '';

        return Notification::where(function ($q) use ($userId, $userRole) {
            $q->where('penerima_id', $userId)
              ->orWhere('tipe_penerima', 'semua')
              ->orWhere('tipe_penerima', 'all');

            if (in_array($userRole, ['guru', 'siswa', 'admin'])) {
                $q->orWhere('tipe_penerima', $userRole);
            }
        });
    }

    /**
     * Hanya notifikasi yang BELUM dibaca oleh user ini.
     * Gunakan LEFT JOIN ke notification_reads untuk filter.
     */
    private function unreadQuery()
    {
        $userId = auth()->id();

        return $this->baseQuery()
            ->whereNotExists(function ($q) use ($userId) {
                $q->select(DB::raw(1))
                  ->from('notification_reads')
                  ->whereColumn('notification_reads.notification_id', 'notifications.id')
                  ->where('notification_reads.user_id', $userId);
            });
    }

    /**
     * Tampilkan detail satu notifikasi + auto mark-as-read.
     */
    public function show(Notification $notification): View
    {
        $userId   = auth()->id();
        $userRole = auth()->user()->role ?? '';

        $allowed = $notification->penerima_id === $userId
            || in_array($notification->tipe_penerima, ['semua', 'all'])
            || $notification->tipe_penerima === $userRole;

        if (!$allowed) {
            abort(403, 'Anda tidak berhak melihat notifikasi ini.');
        }

        // Auto mark as read — hanya untuk user ini
        $this->markRead($userId, $notification->id);

        // Tambahkan helper property agar view bisa cek status baca
        $notification->is_read_by_me = true;

        return view('notifications.show', compact('notification'));
    }

    /**
     * Tampilkan daftar semua notifikasi user.
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();

        // Ambil semua notifikasi + info sudah dibaca atau belum oleh user ini
        $notifications = $this->baseQuery()
            ->with('sender')
            ->leftJoin('notification_reads as nr', function ($join) use ($userId) {
                $join->on('nr.notification_id', '=', 'notifications.id')
                     ->where('nr.user_id', '=', $userId);
            })
            ->select('notifications.*', DB::raw('nr.read_at as my_read_at'))
            ->latest('notifications.created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Jumlah notifikasi belum dibaca user ini (AJAX).
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->unreadQuery()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * 5 notifikasi terbaru yang belum dibaca (AJAX untuk bell dropdown).
     */
    public function recent(): JsonResponse
    {
        $notifications = $this->unreadQuery()
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(fn ($n) => [
                'id'            => $n->id,
                'title'         => $n->judul ?? $n->title ?? 'Notifikasi',
                'message'       => $n->pesan ?? $n->message ?? '',
                'type'          => $n->tipe ?? $n->type ?? 'info',
                'action_url'    => $n->url_aksi ?? $n->action_url ?? '#',
                'time_ago'      => $n->created_at
                                   ? \Carbon\Carbon::parse($n->created_at)->diffForHumans()
                                   : '',
                'tipe_penerima' => $n->tipe_penerima ?? '',
            ])
        ]);
    }

    /**
     * Tandai satu notifikasi sudah dibaca (AJAX).
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

        $this->markRead($userId, $notification->id);

        return response()->json(['success' => true]);
    }

    /**
     * Tandai SEMUA notifikasi sudah dibaca oleh user ini.
     */
    public function markAllAsRead(): JsonResponse
    {
        $userId = auth()->id();

        // Ambil semua ID notifikasi yang belum dibaca oleh user ini
        $unreadIds = $this->unreadQuery()->pluck('notifications.id');

        if ($unreadIds->isNotEmpty()) {
            $rows = $unreadIds->map(fn ($id) => [
                'user_id'         => $userId,
                'notification_id' => $id,
                'read_at'         => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ])->toArray();

            // insertOrIgnore: abaikan duplikat (user mungkin sudah baca sebagian)
            NotificationRead::insertOrIgnore($rows);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Hapus notifikasi (hanya yang personal milik user ini, atau admin).
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

        // Non-admin hanya bisa "sembunyikan" dengan menandai baca — bukan hapus permanen
        // Admin bisa hapus record asli
        if ($userRole === 'admin' && $notification->penerima_id === $userId) {
            $notification->delete();
        } else {
            // Untuk user biasa atau notifikasi broadcast: cukup tandai sudah baca
            $this->markRead($userId, $notification->id);
        }

        return response()->json(['success' => true]);
    }
}
