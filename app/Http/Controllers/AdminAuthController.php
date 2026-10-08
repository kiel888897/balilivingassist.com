<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak cocok.'])
                ->withInput($request->only('email'));
        }

        if (!Auth::user()->hasPermissionTo('admin.access')) {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Akun ini tidak memiliki akses admin.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin'));
    }

    public function logout(Request $request)
    {
        $isAdmin = $request->user() && $request->user()->hasPermissionTo('admin.access');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isAdmin ? 'admin.login' : 'login');
    }
}
