<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\User;
use App\Notifications\NewWaveNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $sender = $this->user('notification-sender@example.com');
        $recipient = $this->user('notifications@example.com');
        $connection = Connection::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => Connection::PENDING,
        ]);

        $recipient->notify(new NewWaveNotification($connection->load('sender')));
        $id = $recipient->notifications()->first()->id;

        $this->actingAs($recipient)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonStructure(['notifications']);

        $this->actingAs($recipient)
            ->postJson('/api/v1/notifications/'.$id.'/read')
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertNotNull($recipient->notifications()->findOrFail($id)->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_read(): void
    {
        $owner = $this->user('notification-owner@example.com');
        $other = $this->user('notification-other@example.com');
        $sender = $this->user('notification-sender2@example.com');
        $connection = Connection::create([
            'sender_id' => $sender->id,
            'recipient_id' => $owner->id,
            'status' => Connection::PENDING,
        ]);
        $owner->notify(new NewWaveNotification($connection->load('sender')));
        $id = $owner->notifications()->first()->id;

        $this->actingAs($other)
            ->postJson('/api/v1/notifications/'.$id.'/read')
            ->assertNotFound();
    }
}
