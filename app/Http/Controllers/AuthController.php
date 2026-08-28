<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

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

    public function redirectToProvider(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'apple', 'x'], true), 404);
        $driver = $provider === 'x' ? Socialite::driver('x')->scopes(['users.read', 'tweet.read']) : Socialite::driver($provider);
        return $driver->redirect();
    }

    public function handleProviderCallback(Request $request, string $provider): RedirectResponse
    {
        if (! in_array($provider, ['google', 'apple', 'x'], true)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            $providerId = (string) $socialUser->getId();
            $account = SocialAccount::where('provider', $provider)->where('provider_id', $providerId)->first();

            if ($account) {
                Auth::login($account->user, true);
                $request->session()->regenerate();
                return redirect()->intended(route('discover'));
            }

            $email = $socialUser->getEmail();
            $user = $email ? User::where('email', $email)->first() : null;

            if ($user && $provider === 'x') {
                return redirect()->route('login')->withErrors([
                    'email' => 'An account already exists with this email. Log in with your password first, then connect X from your account.',
                ]);
            }

            if (! $user) {
                $name = trim((string) ($socialUser->getName() ?: $socialUser->getNickname() ?: 'Lamii user'));
                $email = $email ?: $provider.'-'.Str::lower($providerId).'@accounts.lamii.invalid';
                $user = User::create([
                    'name' => Str::limit($name, 80, ''),
                    'email' => $email,
                    'password' => Str::random(64),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => in_array($provider, ['google', 'apple'], true) ? now() : null,
                    'is_discoverable' => false,
                    'discovery_radius' => 5,
                    'onboarding_completed' => false,
                ]);
            }

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $providerId,
            ]);

            if ($socialUser->getAvatar() && ! $user->avatar) {
                $user->forceFill(['avatar' => $socialUser->getAvatar()])->save();
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            return redirect()->route('onboarding.profile');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('login')->withErrors([
                'email' => 'We could not complete social sign-in. Please try again.',
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
