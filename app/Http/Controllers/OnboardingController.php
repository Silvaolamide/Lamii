<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function profile() { return view('onboarding.profile'); }

    public function saveProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:80'],
            'bio' => ['nullable','string','max:500'],
            'date_of_birth' => ['nullable','date','before:-13 years'],
            'gender' => ['nullable','in:male,female,non_binary,prefer_not_to_say'],
            'avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
        ]);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $request->user()->update($data);
        return redirect()->route('onboarding.interests');
    }

    public function interests() { return view('onboarding.interests', ['interests' => Interest::orderBy('name')->get()]); }

    public function saveInterests(Request $request)
    {
        $data = $request->validate(['interests' => ['required','array','min:1'], 'interests.*' => ['integer','exists:interests,id']]);
        $request->user()->interests()->sync($data['interests']);
        return redirect()->route('onboarding.privacy');
    }

    public function privacy() { return view('onboarding.privacy'); }

    public function savePrivacy(Request $request)
    {
        $data = $request->validate(['is_discoverable' => ['required','boolean'], 'discovery_radius' => ['required','integer','in:1,5,10']]);
        $request->user()->update($data + ['onboarding_completed' => true]);
        return redirect()->route('discover');
    }
}
