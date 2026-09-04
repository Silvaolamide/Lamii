<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'name' => 'API User',
            'email' => 'api-auth@example.com',
            'password' => Hash::make('password'),
            'is_discoverable' => true,
            'discovery_radius' => 5,
            'onboarding_completed' => true,
        ]);
    }

    public function test_login_returns_sanctum_bearer_token(): void
    {
        $this->user();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api-auth@example.com',
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'api-auth@example.com')
            ->assertJsonStructure(['token', 'token_type', 'user']);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->user();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'api-auth@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_api_rejects_requests_without_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_malformed_bearer_token_is_rejected(): void
    {
        $this->withHeader('Authorization', 'Bearer definitely-not-a-valid-token')
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    public function test_bearer_token_authenticates_api_requests(): void
    {
        $user = $this->user();
        $token = $user->createToken('test-device')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = $this->user();
        $token = $user->createToken('test-device')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    public function test_logout_revokes_only_the_current_token(): void
    {
        $user = $this->user();
        $tokenA = $user->createToken('device-a')->plainTextToken;
        $tokenB = $user->createToken('device-b')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();

        $this->withHeader('Authorization', 'Bearer '.$tokenB)
            ->getJson('/api/v1/me')
            ->assertOk();
    }
}
