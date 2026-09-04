<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    private function user(string $email, bool $onboarded = true): User
    {
        return User::create([
            'name' => 'API User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_discoverable' => true,
            'discovery_radius' => 5,
            'onboarding_completed' => $onboarded,
        ]);
    }

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_authenticated_user_can_read_and_update_profile(): void
    {
        $user = $this->user('api@example.com');

        $this->actingAs($user)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'api@example.com');

        $this->actingAs($user)->patchJson('/api/v1/me', [
            'name' => 'Updated Name',
            'bio' => 'Hello from the API',
            'discovery_radius' => 8,
        ])->assertOk()
            ->assertJsonPath('user.name', 'Updated Name')
            ->assertJsonPath('user.bio', 'Hello from the API')
            ->assertJsonPath('user.discovery_radius', 8);
    }

    public function test_api_does_not_bypass_onboarding_guard(): void
    {
        $user = $this->user('incomplete@example.com', false);

        $this->actingAs($user)->getJson('/api/v1/connections')->assertForbidden();
        $this->actingAs($user)->getJson('/api/v1/conversations')->assertForbidden();
    }
}
