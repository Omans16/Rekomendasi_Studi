<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Role
        |--------------------------------------------------------------------------
        | Mendukung penggunaan:
        | role:admin
        | role:guru_bk
        | role:admin,guru_bk
        | role:siswa
        */
        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        /*
        |--------------------------------------------------------------------------
        | Khusus Role Siswa
        |--------------------------------------------------------------------------
        | Sistem siswa hanya diperbolehkan untuk siswa kelas 12.
        */
        if ($user->role === 'siswa' && (int) $user->kelas !== 12) {
            abort(403, 'Akses siswa hanya diperbolehkan untuk kelas 12.');
        }

        return $next($request);
    }
}