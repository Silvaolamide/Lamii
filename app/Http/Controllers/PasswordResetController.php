<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    public function requestForm() { return view('auth.forgot-password'); }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required','email']]);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetForm(string $token, Request $request) { return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]); }

    public function reset(Request $request)
    {
        $data = $request->validate(['token'=>'required','email'=>'required|email','password'=>'required|confirmed|min:8']);
        $status = Password::reset($data, function ($user, $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        return $status === Password::PASSWORD_RESET ? redirect()->route('login')->with('status', 'Your password has been reset.') : back()->withErrors(['email' => __($status)]);
    }
}
