<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:80'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','confirmed',Password::defaults()],
        ]);

        $user = User::create($data + ['is_discoverable' => false, 'discovery_radius' => 5, 'onboarding_completed' => false]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('onboarding.profile');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required','email'], 'password' => ['required','string']]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('discover'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
