<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencegah browser meng-cache halaman (termasuk lewat tombol Back/Forward).
 *
 * Tanpa ini, setelah login, menekan tombol "Back" di browser bisa menampilkan
 * halaman login yang lama dari cache browser (bukan dari server) walaupun
 * session sudah aktif — begitu juga sebaliknya setelah logout, tombol "Back"
 * bisa menampilkan halaman yang butuh login dari cache.
 *
 * Dengan header ini, browser dipaksa selalu meminta ulang ke server saat
 * navigasi Back/Forward, sehingga middleware 'guest' dan 'auth' selalu
 * sempat mengecek ulang status login dan redirect dengan benar.
 */
class PreventBackHistoryCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
