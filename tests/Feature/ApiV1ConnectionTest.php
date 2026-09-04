<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1ConnectionTest extends TestCase
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

    public function test_recipient_can_accept_wave_and_sender_is_notified(): void
    {
        $sender = $this->user('sender@example.com');
        $recipient = $this->user('recipient@example.com');
        $connection = Connection::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => Connection::PENDING,
        ]);

        $this->actingAs($recipient)
            ->postJson('/api/v1/connections/'.$connection->id.'/accept')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('state', 'connected');

        $this->assertDatabaseHas('connections', [
            'id' => $connection->id,
            'status' => Connection::ACCEPTED,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $sender->id,
            'type' => 'App\\Notifications\\ConnectionAcceptedNotification',
        ]);
    }

    public function test_non_recipient_cannot_accept_wave(): void
    {
        $sender = $this->user('sender2@example.com');
        $recipient = $this->user('recipient2@example.com');
        $connection = Connection::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => Connection::PENDING,
        ]);

        $this->actingAs($sender)
            ->postJson('/api/v1/connections/'.$connection->id.'/accept')
            ->assertForbidden();
    }
}
