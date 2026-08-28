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

    public function test_nearby_users_are_returned_and_blocked_users_are_excluded(): void
    {
        $me = $this->user('me@example.com');
        $nearby = $this->user('nearby@example.com');
        $blocked = $this->user('blocked@example.com');

        $location = [6.5244, 3.3792];
        $this->location($nearby, ...$location);
        $this->location($blocked, ...$location);

        Block::create(['blocker_id' => $me->id, 'blocked_id' => $blocked->id]);

        $response = $this->actingAs($me)->getJson('/discover/nearby?latitude=6.5244&longitude=3.3792');

        $response->assertOk()
            ->assertJsonPath('radius_km', 5)
            ->assertJsonCount(1, 'people')
            ->assertJsonPath('people.0.id', $nearby->id);
    }

    public function test_expired_locations_are_not_returned(): void
    {
        $me = $this->user('me2@example.com');
        $expired = $this->user('expired@example.com');

        $this->location($expired, 6.5244, 3.3792, now()->subMinute());

        $response = $this->actingAs($me)->getJson('/discover/nearby?latitude=6.5244&longitude=3.3792');

        $response->assertOk()->assertJsonCount(0, 'people');
    }
}
