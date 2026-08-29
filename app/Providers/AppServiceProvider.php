<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\ViewComposers\NotificationComposer;
use App\Http\ViewComposers\GuruStatsComposer;
use App\Http\ViewComposers\GuruDashboardComposer;
use App\Http\ViewComposers\AdminStatsComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Header composer — suntikkan notifications, unreadCount, stats ke semua partial header
        View::composer([
            'partials.header-admin',
            'partials.header-guru',
            'partials.header-siswa',
        ], \App\Http\ViewComposers\HeaderComposer::class);

        // Register view composers (legacy — tetap ada untuk backward compat)
        View::composer('partials.notifications', NotificationComposer::class);
        View::composer('layouts.admin', NotificationComposer::class);
        View::composer('layouts.guru', NotificationComposer::class);
        View::composer('layouts.siswa', NotificationComposer::class);

        // Register guru stats composer for sidebar
        View::composer('partials.sidebar-guru', GuruStatsComposer::class);
        View::composer('layouts.guru', GuruStatsComposer::class);

        // Supply upcoming exams and related data for Guru views
        View::composer(['layouts.guru', 'partials.header-guru', 'guru.dashboard'], GuruDashboardComposer::class);

        // Register admin stats composer for admin layout & sidebar (can be disabled via env)
        if (!env('ADMIN_STATS_DISABLE', false)) {
            View::composer('layouts.admin', AdminStatsComposer::class);
            View::composer('partials.sidebar-admin', AdminStatsComposer::class);
        }
    }
}
