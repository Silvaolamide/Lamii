<?php

use App\Broadcasting\ConversationChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversation}', ConversationChannel::class);
