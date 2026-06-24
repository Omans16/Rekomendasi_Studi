<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'siswa') {
                return redirect()->route('siswa.dashboard');
            }

            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'nisn'     => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nisn.required'     => 'NISN wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = [
            'nisn'     => trim($validated['nisn']),
            'password' => $validated['password'],
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('nisn'))
                ->with('error', 'NISN atau password salah.');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role === 'siswa') {
            return redirect()->intended(route('siswa.dashboard'));
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /*
    | Register Siswa Dinonaktifkan
    */
    public function showRegister()
    {
        return redirect()
            ->route('login')
            ->with('error', 'Registrasi siswa hanya dapat dilakukan oleh Guru BK.');
    }

    public function register(Request $request)
    {
        return redirect()
            ->route('login')
            ->with('error', 'Registrasi siswa hanya dapat dilakukan oleh Guru BK.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil logout.');
    }
}