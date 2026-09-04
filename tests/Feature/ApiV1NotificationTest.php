<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewWaveNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $email): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_discoverable' => true,
            'discovery_radius' => 5,
            'onboarding_completed' => true,
        ]);
    }

    public function test_notifications_can_be_listed_and_marked_read(): void
    {
        $user = $this->user('notifications@example.com');
        $notification = Notification::route('database', $user->id);
        unset($notification);

        $user->notify(new NewWaveNotification(
            \App\Models\Connection::create([
                'sender_id' => $user->id,
                'recipient_id' => $user->id,
                'status' => \App\Models\Connection::PENDING,
            ])
        ));

        $id = $user->notifications()->first()->id;

        $this->actingAs($user)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonStructure(['notifications']);

        $this->actingAs($user)
            ->postJson('/api/v1/notifications/'.$id.'/read')
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('notifications', ['id' => $id, 'read_at' => now()->toDateTimeString()]);
    }
}
