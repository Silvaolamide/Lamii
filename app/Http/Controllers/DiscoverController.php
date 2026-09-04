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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $radius = max(1, min((int) $user->discovery_radius, 10));
        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];
        $page = (int) ($data['page'] ?? 1);
        $perPage = (int) ($data['per_page'] ?? 20);

        $blockedIds = Block::query()
            ->where(fn ($query) => $query
                ->where('blocker_id', $user->id)
                ->orWhere('blocked_id', $user->id))
            ->get(['blocker_id', 'blocked_id'])
            ->flatMap(fn ($block) => [$block->blocker_id, $block->blocked_id])
            ->unique()
            ->reject(fn ($id) => (int) $id === (int) $user->id)
            ->values();

        // Keep the database query portable across SQLite (CI/tests), MySQL and
        // other supported databases. The bounding box limits candidates, then
        // the Haversine calculation below determines the exact distance.
        $latitudeDelta = $radius / 111.32;
        $longitudeScale = max(cos(deg2rad($latitude)), 0.01);
        $longitudeDelta = $radius / (111.32 * $longitudeScale);
        $minLatitude = max(-90, $latitude - $latitudeDelta);
        $maxLatitude = min(90, $latitude + $latitudeDelta);
        $minLongitude = $longitude - $longitudeDelta;
        $maxLongitude = $longitude + $longitudeDelta;

        $candidateQuery = User::query()
            ->select('users.id', 'users.name', 'users.avatar', 'users.bio')
            ->join('user_locations', 'user_locations.user_id', '=', 'users.id')
            ->where('users.id', '!=', $user->id)
            ->where('users.is_discoverable', true)
            ->where('users.onboarding_completed', true)
            ->where('user_locations.expires_at', '>', now())
            ->whereBetween('user_locations.latitude', [$minLatitude, $maxLatitude])
            ->whereBetween('user_locations.longitude', [$minLongitude, $maxLongitude])
            ->when($blockedIds->isNotEmpty(), fn ($query) => $query->whereNotIn('users.id', $blockedIds));

        // The current location model is expected to have one active location per
        // user. If duplicate rows exist, keep the nearest candidate per user.
        $people = $candidateQuery->get()->map(function ($person) use ($latitude, $longitude) {
            $person->distance_km = $this->distanceInKilometres(
                $latitude,
                $longitude,
                (float) $person->getAttribute('latitude'),
                (float) $person->getAttribute('longitude')
            );

            return $person;
        });

        // The coordinates are only used internally for distance calculation and
        // are never exposed in the API response.
        $people = $people
            ->filter(fn ($person) => $person->distance_km <= $radius)
            ->sortBy(fn ($person) => [(float) $person->distance_km, (int) $person->id])
            ->values();

        $total = $people->count();
        $people = $people
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

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
        })->values();

        return response()->json([
            'people' => $people,
            'radius_km' => $radius,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    private function distanceInKilometres(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        $earthRadius = 6371.0;
        $dLatitude = deg2rad($latitude2 - $latitude1);
        $dLongitude = deg2rad($longitude2 - $longitude1);
        $a = sin($dLatitude / 2) ** 2
            + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLongitude / 2) ** 2;

        return $earthRadius * 2 * asin(min(1, sqrt($a)));
    }

    private function formatDistance(float $kilometres): string
    {
        if ($kilometres < 1) {
            return max(50, (int) (round($kilometres * 1000 / 50) * 50)).' m away';
        }

        return number_format(round($kilometres, 1), 1).' km away';
    }
}
