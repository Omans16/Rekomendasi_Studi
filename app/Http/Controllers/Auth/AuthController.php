<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\HasilPrediksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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

    public function showRegister()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'siswa') {
                return redirect()->route('siswa.dashboard');
            }

            return redirect()->route('admin.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nisn'     => ['required', 'string', 'max:30', 'unique:users,nisn'],
            'name'     => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'nisn.required'      => 'NISN wajib diisi.',
            'nisn.unique'        => 'NISN sudah terdaftar.',
            'name.required'      => 'Nama wajib diisi.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $user = User::create([
            'nisn'     => trim($validated['nisn']),
            'name'     => $validated['name'],
            'password' => Hash::make($validated['password']),
            'role'     => 'siswa',
            'kelas'    => 12,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Hubungkan Hasil Upload Berdasarkan NISN
        |--------------------------------------------------------------------------
        | Jika sebelumnya Admin/Guru BK sudah upload data siswa,
        | maka hasil prediksi yang user_id-nya masih kosong akan otomatis
        | dikaitkan ke akun siswa yang baru dibuat.
        */
        if (!empty($user->nisn)) {
            HasilPrediksi::where('nisn', $user->nisn)
                ->whereNull('user_id')
                ->update([
                    'user_id' => $user->id,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Setelah register berhasil → arahkan ke halaman login
        |--------------------------------------------------------------------------
        | Tidak auto-login; siswa harus login secara manual.
        */
        return redirect()
            ->route('login')
            ->with('success', 'Akun berhasil dibuat! Silakan masuk menggunakan NISN dan password kamu.');
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