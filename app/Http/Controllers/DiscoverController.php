<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function index(Request $request)
    {
        return view('discover.index', ['user' => $request->user()]);
    }

    public function nearby(Request $request)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $user = $request->user();
        $radius = max(1, min((int) $user->discovery_radius, 10));
        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];

        // Clamp the acos input to [-1, 1]. Floating-point rounding can otherwise
        // produce a value such as 1.0000000001 for nearby/same-location users,
        // causing MySQL to return NULL from ACOS and exclude valid matches.
        $distanceExpression = 'LEAST(1, GREATEST(-1, cos(radians(?)) * cos(radians(user_locations.latitude)) * cos(radians(user_locations.longitude) - radians(?)) + sin(radians(?)) * sin(radians(user_locations.latitude))))';
        $distance = "(6371 * acos({$distanceExpression}))";

        $people = User::query()
            ->select('users.id', 'users.name', 'users.avatar', 'users.bio')
            ->selectRaw("{$distance} AS distance_km", [$latitude, $longitude, $latitude])
            ->join('user_locations', 'user_locations.user_id', '=', 'users.id')
            ->where('users.id', '!=', $user->id)
            ->where('users.is_discoverable', true)
            ->where('users.onboarding_completed', true)
            ->where('user_locations.expires_at', '>', now())
            ->having('distance_km', '<=', $radius)
            ->orderBy('distance_km')
            ->limit(50)
            ->get()
            ->map(fn ($person) => [
                'id' => $person->id,
                'name' => $person->name,
                'avatar' => $person->avatar ? asset('storage/'.$person->avatar) : null,
                'bio' => $person->bio,
                'distance' => $this->formatDistance((float) $person->distance_km),
            ]);

        return response()->json(['people' => $people, 'radius_km' => $radius]);
    }

    private function formatDistance(float $kilometres): string
    {
        if ($kilometres < 1) {
            return max(50, (int) (round($kilometres * 1000 / 50) * 50)).' m away';
        }

        return number_format(round($kilometres, 1), 1).' km away';
    }
}
