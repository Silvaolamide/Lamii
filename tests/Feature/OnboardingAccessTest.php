<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OnboardingAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_users_are_redirected_to_onboarding(): void
    {
        $user = User::create([
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => Hash::make('password'),
            'is_discoverable' => false,
            'discovery_radius' => 5,
            'onboarding_completed' => false,
        ]);

        $this->actingAs($user)
            ->get('/discover')
            ->assertRedirect(route('onboarding.profile'));
    }

    public function test_completed_users_can_access_discover(): void
    {
        $user = User::create([
            'name' => 'Complete User',
            'email' => 'complete@example.com',
            'password' => Hash::make('password'),
            'is_discoverable' => true,
            'discovery_radius' => 5,
            'onboarding_completed' => true,
        ]);

        $this->actingAs($user)
            ->get('/discover')
            ->assertOk();
    }
}
