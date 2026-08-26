<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $request->user()->location()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy' => $data['accuracy'] ?? null,
                'expires_at' => now()->addMinutes(15),
            ]
        );

        return response()->json([
            'ok' => true,
            'expires_at' => now()->addMinutes(15)->toIso8601String(),
        ]);
    }

    public function destroy(Request $request)
    {
        $request->user()->location()->delete();

        return response()->json(['ok' => true]);
    }
}
