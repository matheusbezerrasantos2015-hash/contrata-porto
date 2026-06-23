<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        // ── Rate Limiting para rotas de autenticação ──────────────────────────

        // Login: máximo 5 tentativas por minuto (por email + IP)
        RateLimiter::for('auth_login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email') . $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Muitas tentativas. Tente novamente em 1 minuto.',
                    ], 429);
                });
        });

        // Recover / Resend: máximo 3 tentativas por minuto (por email + IP)
        RateLimiter::for('auth_recover', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->input('email') . $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Limite de recuperação atingido. Tente em 1 minuto.',
                    ], 429);
                });
        });
    }
}
