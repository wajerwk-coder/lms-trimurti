<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationComposer
{
    /**
     * Suntik $notifications dan $unreadCount ke setiap view yang terdaftar.
     *
     * Status baca per-user diambil dari tabel notification_reads (bukan
     * dari kolom read_at di notifications yang di-share semua user).
     */
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with(['notifications' => collect(), 'unreadCount' => 0]);
            return;
        }

        $user     = Auth::user();
        $userId   = $user->id;
        $userRole = $user->role ?? '';

        try {
            // ── Query dasar: notifikasi yang relevan untuk user ini ──────────
            $baseWhere = function ($q) use ($userId, $userRole) {
                $q->where('penerima_id', $userId)
                  ->orWhere('tipe_penerima', 'semua')
                  ->orWhere('tipe_penerima', 'all');

                if (in_array($userRole, ['guru', 'siswa', 'admin'])) {
                    $q->orWhere('tipe_penerima', $userRole);
                }
            };

            // ── 10 notifikasi terbaru yang BELUM dibaca oleh user ini ───────
            // Gunakan LEFT JOIN ke notification_reads agar per-user,
            // bukan bergantung pada kolom read_at di tabel notifications.
            $notifications = Notification::where($baseWhere)
                ->leftJoin('notification_reads as nr', function ($join) use ($userId) {
                    $join->on('nr.notification_id', '=', 'notifications.id')
                         ->where('nr.user_id', '=', $userId);
                })
                ->select(
                    'notifications.*',
                    DB::raw('nr.read_at as my_read_at') // NULL = belum dibaca oleh user ini
                )
                ->orderBy('notifications.created_at', 'desc')
                ->limit(10)
                ->get();

            // ── Hitung yang belum dibaca: my_read_at IS NULL ─────────────────
            $unreadCount = $notifications->whereNull('my_read_at')->count();

            // ── Tambahkan helper property 'read_at' per-user ke setiap notif ─
            // Agar view tetap bisa pakai {{ is_null($notif->read_at) ? 'unread' : '' }}
            // tanpa perubahan di view
            $notifications->each(function ($n) {
                // Override read_at dengan status baca personal user ini
                $n->read_at = $n->my_read_at;
            });

            $view->with(compact('notifications', 'unreadCount'));

        } catch (\Throwable $e) {
            $view->with(['notifications' => collect(), 'unreadCount' => 0]);
        }
    }
}
