<?php

namespace Tests\Feature;

use App\Broadcasting\ConversationChannel;
use App\Events\MessageSent;
use App\Models\Block;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReverbBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_sent_uses_a_private_conversation_channel(): void
    {
        $sender = $this->createUser('Sender');
        $recipient = $this->createUser('Recipient');

        Connection::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => Connection::ACCEPTED,
        ]);

        $conversation = Conversation::create([
            'user_one_id' => $sender->id,
            'user_two_id' => $recipient->id,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => 'Realtime hello',
        ]);

        $event = new MessageSent($message);

        $this->assertSame('message.sent', $event->broadcastAs());
        $this->assertSame('private-conversation.'.$conversation->id, $event->broadcastOn()[0]->name);
        $this->assertSame([
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'body' => 'Realtime hello',
            'created_at' => $message->created_at->toIso8601String(),
        ], $event->broadcastWith());
    }

    public function test_private_conversation_channel_rejects_non_participants(): void
    {
        $sender = $this->createUser('Sender');
        $recipient = $this->createUser('Recipient');
        $outsider = $this->createUser('Outsider');

        Connection::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => Connection::ACCEPTED,
        ]);

        $conversation = Conversation::create([
            'user_one_id' => $sender->id,
            'user_two_id' => $recipient->id,
        ]);

        $channel = new ConversationChannel();

        $this->assertTrue($channel->join($sender, $conversation));
        $this->assertTrue($channel->join($recipient, $conversation));
        $this->assertFalse($channel->join($outsider, $conversation));
    }

    public function test_private_conversation_channel_rejects_blocked_connections(): void
    {
        $sender = $this->createUser('Sender');
        $recipient = $this->createUser('Recipient');

        Connection::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => Connection::ACCEPTED,
        ]);

        $conversation = Conversation::create([
            'user_one_id' => $sender->id,
            'user_two_id' => $recipient->id,
        ]);

        Block::create([
            'blocker_id' => $sender->id,
            'blocked_id' => $recipient->id,
        ]);

        $this->assertFalse((new ConversationChannel())->join($sender, $conversation));
    }

    private function createUser(string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower($name).'@example.test',
            'password' => 'password',
            'onboarding_completed' => true,
            'is_discoverable' => true,
        ]);
    }
}
