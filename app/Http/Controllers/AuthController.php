<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
class AuthController extends Controller {
 public function redirect(string $provider){ return Socialite::driver($provider==='x'?'twitter-oauth-2':$provider)->redirect(); }
 public function callback(string $provider){ $driver=Socialite::driver($provider==='x'?'twitter-oauth-2':$provider); $social=$driver->user(); $user=User::updateOrCreate(['email'=>$social->getEmail()],['name'=>$social->getName() ?: $social->getNickname() ?: 'NearWave user','avatar'=>$social->getAvatar(),'provider'=>$provider,'provider_id'=>$social->getId()]); Auth::login($user,true); return redirect()->route('dashboard'); }
 public function logout(Request $request){ Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/'); }
}
