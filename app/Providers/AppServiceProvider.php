<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        // Batas umum API: 60 request per menit per pengguna (per IP jika belum login)
        RateLimiter::for('api-pengguna', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Terlalu banyak permintaan. Coba lagi dalam beberapa saat.',
                    ], 429, $headers);
                });
        });

        // Batas login dan registrasi: 5 percobaan per menit per kombinasi email + IP
        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(strtolower((string) $request->input('email')) . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.',
                    ], 429, $headers);
                });
        });
    }
}
