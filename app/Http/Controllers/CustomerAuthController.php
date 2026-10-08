<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.customer-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => __('site.auth.invalid_credentials')])
                ->withInput($request->only('email'));
        }

        $user = Auth::user();

        if (!$user->hasRole('customer') || $user->hasPermissionTo('admin.access')) {
            Auth::logout();

            return back()
                ->withErrors(['email' => __('site.auth.customer_only')])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()->intended(route('customer.account'));
    }

    public function showRegister()
    {
        return view('auth.customer-register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $customerRole = Role::where('slug', 'customer')->firstOrFail();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->roles()->attach($customerRole);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('customer.account'));
    }

    public function account()
    {
        abort_unless(Auth::user()->hasRole('customer'), 403);

        $user = Auth::user();

        return view('customer.account', [
            'user' => $user,
            'quotationCount' => $user->quotations()->whereNotIn('status', \App\Models\Quotation::ORDER_STATUSES)->count(),
            'orderCount' => $user->quotations()->whereIn('status', \App\Models\Quotation::ORDER_STATUSES)->count(),
        ]);
    }
}
