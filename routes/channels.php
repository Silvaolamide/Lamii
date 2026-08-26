<?php

use App\Models\Connection;
use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (! $conversation || ! in_array($user->id, [$conversation->user_one_id, $conversation->user_two_id], true)) return false;

    return Connection::where('status', Connection::ACCEPTED)
        ->where(fn ($q) => $q->where(fn ($q) => $q->where('sender_id', $conversation->user_one_id)->where('recipient_id', $conversation->user_two_id))
            ->orWhere(fn ($q) => $q->where('sender_id', $conversation->user_two_id)->where('recipient_id', $conversation->user_one_id)))
        ->exists();
});
