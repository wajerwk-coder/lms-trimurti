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
     * Tampilkan semua notifikasi user yang sedang login.
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $notifications = Notification::where(function ($q) use ($userId) {
                $q->where('penerima_id', $userId)
                  ->orWhere('tipe_penerima', 'semua');
            })
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Jumlah notifikasi yang belum dibaca (AJAX).
     */
    public function unreadCount(): JsonResponse
    {
        $userId = auth()->id();

        $count = Notification::where(function ($q) use ($userId) {
                $q->where('penerima_id', $userId)
                  ->orWhere('tipe_penerima', 'semua');
            })
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * 5 notifikasi terbaru yang belum dibaca (AJAX untuk bell dropdown).
     */
    public function recent(): JsonResponse
    {
        $userId = auth()->id();

        $notifications = Notification::where(function ($q) use ($userId) {
                $q->where('penerima_id', $userId)
                  ->orWhere('tipe_penerima', 'semua');
            })
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
                'time_ago'   => $n->created_at?->diffForHumans() ?? '',
            ])
        ]);
    }

    /**
     * Tandai notifikasi tertentu sudah dibaca.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        if ($notification->penerima_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Tandai semua notifikasi sudah dibaca.
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::where('penerima_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Hapus notifikasi.
     */
    public function delete(Notification $notification): JsonResponse
    {
        if ($notification->penerima_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
