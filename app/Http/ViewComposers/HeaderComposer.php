<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AssignmentSubmission;

class HeaderComposer
{
    /**
     * Suntikkan variabel yang dibutuhkan oleh semua partial header
     * agar tidak crash jika halaman tidak menyertakannya.
     */
    public function compose(View $view): void
    {
        if (!Auth::check()) return;

        try {
            $userId = Auth::id();

            // Paksa reload user dari DB agar photo_url selalu fresh (tidak pakai cached object)
            $freshUser = \App\Models\UserCentral::find($userId);
            if ($freshUser) {
                Auth::setUser($freshUser);
            }

            $role = Auth::user()->role ?? 'guest';

            // ── Notifikasi ─────────────────────────────────────────
            $notifications = collect();
            $unreadCount   = 0;

            if (DB::getSchemaBuilder()->hasTable('notifications')) {
                // Coba ambil notifikasi via penerima_id atau receiver_id
                $cols = DB::getSchemaBuilder()->getColumnListing('notifications');

                $recipientCol = in_array('penerima_id', $cols) ? 'penerima_id'
                    : (in_array('receiver_id', $cols) ? 'receiver_id' : null);

                if ($recipientCol) {
                    $notifications = DB::table('notifications')
                        ->where($recipientCol, $userId)
                        ->whereNull('deleted_at')
                        ->orderByDesc('created_at')
                        ->limit(10)
                        ->get();

                    $readCol = in_array('read_at', $cols) ? 'read_at'
                        : (in_array('is_read', $cols) ? 'is_read' : null);

                    if ($readCol === 'read_at') {
                        $unreadCount = DB::table('notifications')
                            ->where($recipientCol, $userId)
                            ->whereNull('read_at')
                            ->whereNull('deleted_at')
                            ->count();
                    } elseif ($readCol === 'is_read') {
                        $unreadCount = DB::table('notifications')
                            ->where($recipientCol, $userId)
                            ->where('is_read', false)
                            ->whereNull('deleted_at')
                            ->count();
                    }
                }
            }

            $view->with(compact('notifications', 'unreadCount'));

            // ── Stats per role ─────────────────────────────────────
            if ($role === 'guru') {
                $pendingGrading = 0;
                try {
                    $pendingGrading = AssignmentSubmission::join(
                        'assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id'
                    )
                    ->where('assignments.guru_id', $userId)
                    ->whereNull('assignment_submissions.score')
                    ->count();
                } catch (\Throwable $e) {
                    // Silent fail — jangan crash header karena stats
                }

                $view->with('stats', ['pending_grading' => $pendingGrading]);
            }

        } catch (\Throwable $e) {
            // Header tidak boleh crash karena error notifikasi/stats
            Log::warning('HeaderComposer error: ' . $e->getMessage());
            $view->with([
                'notifications' => collect(),
                'unreadCount'   => 0,
                'stats'         => [],
            ]);
        }
    }
}
