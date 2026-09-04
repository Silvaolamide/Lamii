<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_discoverable' => ['sometimes', 'boolean'],
            'discovery_radius' => ['sometimes', 'integer', 'between:1,10'],
        ]);

        $request->user()->update($data);

        return response()->json(['user' => $this->userPayload($request->user()->fresh())]);
    }

    private function userPayload($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null,
            'bio' => $user->bio,
            'is_discoverable' => (bool) $user->is_discoverable,
            'discovery_radius' => (int) $user->discovery_radius,
            'onboarding_completed' => (bool) $user->onboarding_completed,
        ];
    }
}
