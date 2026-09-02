<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Percayai semua proxy (Railway berjalan di belakang load balancer/proxy)
        // sehingga header X-Forwarded-Proto dari Railway dikenali dan URL::forceScheme('https') bekerja dengan benar.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guru' => \App\Http\Middleware\GuruMiddleware::class,
            'siswa' => \App\Http\Middleware\SiswaMiddleware::class,
            // ❌ COMMENT MIDDLEWARE YANG TIDAK DIBUTUHKAN
            // 'active_student' => \App\Http\Middleware\ActiveStudentMiddleware::class,
            // 'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            // 'cache_response' => \App\Http\Middleware\CacheResponseMiddleware::class,
            // 'maintenance' => \App\Http\Middleware\MaintenanceModeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
