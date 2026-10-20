<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class CustomerPasswordResetController extends Controller
{
    public function requestForm()
    {
        return view('auth.password-email');
    }

    public function sendLink(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $data['email'])->first();

        if ($user && $user->hasRole('customer') && !$user->hasPermissionTo('admin.access')) {
            try {
                Password::sendResetLink(['email' => $data['email']]);
            } catch (Throwable $exception) {
                Log::error('Failed to send customer password reset email.', [
                    'user_id' => $user->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return back()->with('status', __('site.auth.password_reset_request_status'));
    }

    public function resetForm(Request $request, string $token)
    {
        return view('auth.password-reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => __($status),
        ]);
    }
}
