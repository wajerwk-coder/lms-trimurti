<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with(['notifications' => collect(), 'unreadCount' => 0]);
            return;
        }

        $user = Auth::user();

        try {
            // Gunakan Eloquent agar created_at otomatis di-cast ke Carbon
            $notifications = Notification::where(function($q) use ($user) {
                    $q->where('penerima_id', $user->id)
                      ->orWhere('tipe_penerima', 'semua');
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $unreadCount = Notification::where(function($q) use ($user) {
                    $q->where('penerima_id', $user->id)
                      ->orWhere('tipe_penerima', 'semua');
                })
                ->where(function($q) {
                    $q->whereNull('read_at')
                      ->orWhere('is_read', false);
                })
                ->count();

            $view->with(compact('notifications', 'unreadCount'));

        } catch (\Throwable $e) {
            $view->with(['notifications' => collect(), 'unreadCount' => 0]);
        }
    }
}
