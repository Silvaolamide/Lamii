<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DiscoveryTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $email, bool $discoverable = true): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_discoverable' => $discoverable,
            'discovery_radius' => 5,
            'onboarding_completed' => true,
        ]);
    }

    private function location(User $user, float $latitude, float $longitude, ?string $expiresAt = null): void
    {
        UserLocation::create([
            'user_id' => $user->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => 20,
            'expires_at' => $expiresAt ?? now()->addMinutes(15),
        ]);
    }

    public function test_nearby_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/discover/nearby?latitude=6.5244&longitude=3.3792')->assertUnauthorized();
    }

    public function test_nearby_users_are_returned_and_blocked_users_are_excluded(): void
    {
        $me = $this->user('me@example.com');
        $nearby = $this->user('nearby@example.com');
        $blocked = $this->user('blocked@example.com');

        $location = [6.5244, 3.3792];
        $this->location($nearby, ...$location);
        $this->location($blocked, ...$location);

        Block::create(['blocker_id' => $me->id, 'blocked_id' => $blocked->id]);

        $response = $this->actingAs($me)->getJson('/api/v1/discover/nearby?latitude=6.5244&longitude=3.3792');

        $response->assertOk()
            ->assertJsonPath('radius_km', 5)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonCount(1, 'people')
            ->assertJsonPath('people.0.id', $nearby->id)
            ->assertJsonMissingPath('people.0.email');
    }

    public function test_users_outside_radius_are_not_returned(): void
    {
        $me = $this->user('radius@example.com');
        $far = $this->user('far@example.com');

        $this->location($far, 6.6, 3.3792);

        $this->actingAs($me)
            ->getJson('/api/v1/discover/nearby?latitude=6.5244&longitude=3.3792')
            ->assertOk()
            ->assertJsonCount(0, 'people');
    }

    public function test_non_discoverable_and_expired_locations_are_not_returned(): void
    {
        $me = $this->user('filters@example.com');
        $hidden = $this->user('hidden@example.com', false);
        $expired = $this->user('expired@example.com');

        $this->location($hidden, 6.5244, 3.3792);
        $this->location($expired, 6.5244, 3.3792, now()->subMinute());

        $this->actingAs($me)
            ->getJson('/api/v1/discover/nearby?latitude=6.5244&longitude=3.3792')
            ->assertOk()
            ->assertJsonCount(0, 'people');
    }

    public function test_blocking_works_in_both_directions_and_self_is_excluded(): void
    {
        $me = $this->user('self@example.com');
        $blockedMe = $this->user('blocked-me@example.com');
        $visible = $this->user('visible@example.com');

        $location = [6.5244, 3.3792];
        $this->location($me, ...$location);
        $this->location($blockedMe, ...$location);
        $this->location($visible, ...$location);

        Block::create(['blocker_id' => $blockedMe->id, 'blocked_id' => $me->id]);

        $response = $this->actingAs($me)->getJson('/api/v1/discover/nearby?latitude=6.5244&longitude=3.3792');

        $response->assertOk()->assertJsonCount(1, 'people')->assertJsonPath('people.0.id', $visible->id);
    }

    public function test_pagination_has_stable_order_and_safe_profile_shape(): void
    {
        $me = $this->user('page@example.com');

        foreach (range(1, 3) as $i) {
            $person = $this->user("person{$i}@example.com");
            $this->location($person, 6.5244 + ($i * 0.0001), 3.3792);
        }

        $response = $this->actingAs($me)->getJson('/api/v1/discover/nearby?latitude=6.5244&longitude=3.3792&per_page=2&page=2');

        $response->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(1, 'people')
            ->assertJsonStructure([
                'people' => [[
                    'id',
                    'name',
                    'avatar',
                    'bio',
                    'distance',
                    'connection_state',
                    'connection_id',
                ]],
            ]);
    }
}
