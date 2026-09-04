<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1ChatTest extends TestCase
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

    private function connect(User $a, User $b): Connection
    {
        return Connection::create([
            'sender_id' => $a->id,
            'recipient_id' => $b->id,
            'status' => Connection::ACCEPTED,
            'responded_at' => now(),
        ]);
    }

    public function test_connected_users_can_start_and_send_messages(): void
    {
        $me = $this->user('chat-me@example.com');
        $other = $this->user('chat-other@example.com');
        $this->connect($me, $other);

        $start = $this->actingAs($me)
            ->postJson('/api/v1/conversations/'.$other->id)
            ->assertOk()
            ->assertJsonStructure(['conversation' => ['id', 'last_message_at', 'user']]);

        $conversationId = $start->json('conversation.id');

        $this->actingAs($me)
            ->postJson('/api/v1/conversations/'.$conversationId.'/messages', ['body' => 'Hello there'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Hello there')
            ->assertJsonPath('message.sender_id', $me->id);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $other->id,
            'type' => 'App\\Notifications\\NewMessageNotification',
        ]);
    }

    public function test_unconnected_users_cannot_start_chat(): void
    {
        $me = $this->user('no-chat-me@example.com');
        $other = $this->user('no-chat-other@example.com');

        $this->actingAs($me)
            ->postJson('/api/v1/conversations/'.$other->id)
            ->assertForbidden();
    }

    public function test_non_participant_cannot_read_conversation(): void
    {
        $me = $this->user('participant-a@example.com');
        $other = $this->user('participant-b@example.com');
        $outsider = $this->user('outsider@example.com');
        $this->connect($me, $other);
        $conversation = Conversation::create([
            'user_one_id' => min($me->id, $other->id),
            'user_two_id' => max($me->id, $other->id),
        ]);

        $this->actingAs($outsider)
            ->getJson('/api/v1/conversations/'.$conversation->id.'/messages')
            ->assertForbidden();
    }
}
