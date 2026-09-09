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
            $role = Auth::user()->role ?? 'guest';

            // Ambil foto terbaru langsung dari DB (tidak lewat Auth cached object)
            $freshPhoto = DB::table('users_central')->where('id', $userId)->value('photo');
            $view->with('freshUserPhoto', $freshPhoto);

            // ── Notifikasi ─────────────────────────────────────────
            $notifications = collect();
            $unreadCount   = 0;

            if (DB::getSchemaBuilder()->hasTable('notifications')) {
                // Gunakan Eloquent agar created_at di-cast ke Carbon
                try {
                    $notifications = \App\Models\Notification::where('penerima_id', $userId)
                        ->whereNull('deleted_at')
                        ->orderByDesc('created_at')
                        ->limit(10)
                        ->get();

                    $unreadCount = \App\Models\Notification::where('penerima_id', $userId)
                        ->whereNull('deleted_at')
                        ->where(function($q) {
                            $q->whereNull('read_at')->orWhere('is_read', false);
                        })
                        ->count();
                } catch (\Throwable $e) {
                    // Silent fail
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
