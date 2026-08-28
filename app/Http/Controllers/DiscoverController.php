<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Connection;
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

        $blockedIds = Block::query()
            ->where(fn ($query) => $query
                ->where('blocker_id', $user->id)
                ->orWhere('blocked_id', $user->id))
            ->get(['blocker_id', 'blocked_id'])
            ->flatMap(fn ($block) => [$block->blocker_id, $block->blocked_id])
            ->unique()
            ->reject(fn ($id) => (int) $id === (int) $user->id)
            ->values();

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
            ->when($blockedIds->isNotEmpty(), fn ($query) => $query->whereNotIn('users.id', $blockedIds))
            ->having('distance_km', '<=', $radius)
            ->orderBy('distance_km')
            ->limit(50)
            ->get();

        $otherIds = $people->pluck('id');
        $relationships = Connection::query()
            ->where(function ($query) use ($user, $otherIds) {
                $query->where('sender_id', $user->id)->whereIn('recipient_id', $otherIds)
                    ->orWhere(function ($q) use ($user, $otherIds) {
                        $q->where('recipient_id', $user->id)->whereIn('sender_id', $otherIds);
                    });
            })
            ->get(['id', 'sender_id', 'recipient_id', 'status'])
            ->keyBy(function ($connection) use ($user) {
                return $connection->sender_id == $user->id ? $connection->recipient_id : $connection->sender_id;
            });

        $people = $people->map(function ($person) use ($relationships, $user) {
            $connection = $relationships->get($person->id);
            $state = 'none';
            $connectionId = null;

            if ($connection) {
                $connectionId = $connection->id;
                if ($connection->status === Connection::ACCEPTED) {
                    $state = 'connected';
                } elseif ($connection->status === Connection::PENDING) {
                    $state = $connection->sender_id == $user->id ? 'sent' : 'incoming';
                } elseif ($connection->status === Connection::DECLINED) {
                    $state = 'declined';
                }
            }

            return [
                'id' => $person->id,
                'name' => $person->name,
                'avatar' => $person->avatar ? asset('storage/'.$person->avatar) : null,
                'bio' => $person->bio,
                'distance' => $this->formatDistance((float) $person->distance_km),
                'connection_state' => $state,
                'connection_id' => $connectionId,
            ];
        });

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
