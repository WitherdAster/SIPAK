<?php

namespace App\Http\Controllers;

use App\Models\Aplikasi;
use App\Models\PerangkatDaerah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function show(): View
    {
        $totalAplikasi = Aplikasi::count();
        $totalSkpd = PerangkatDaerah::count();
        $terverifikasi = Aplikasi::terverifikasi()->count();

        return view('login', compact('totalAplikasi', 'totalSkpd', 'terverifikasi'));
    }

    /**
     * Proses login. Menerima username/NIP atau email di field yang sama.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
            'captcha' => ['required', 'string', 'max:10'],
        ], [
            'captcha.required' => 'Kode verifikasi wajib diisi.',
        ]);

        $kodeCaptcha = (string) $request->session()->pull(CaptchaController::SESSION_KEY, '');
        $jawaban = strtoupper(trim($validated['captcha']));

        if ($kodeCaptcha === '' || ! hash_equals($kodeCaptcha, $jawaban)) {
            return back()
                ->withErrors(['captcha' => 'Kode verifikasi salah atau sudah kedaluwarsa. Silakan coba lagi.'])
                ->onlyInput('username', 'remember');
        }

        $login = $validated['username'];
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $login,
            'password' => $validated['password'],
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['username' => 'Username / NIP atau password salah.'])
            ->onlyInput('username', 'remember');
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
