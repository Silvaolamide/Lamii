<?php

namespace App\Notifications;

use App\Models\Connection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConnectionAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public Connection $connection) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $otherUser = $this->connection->sender_id === $notifiable->id
            ? $this->connection->recipient
            : $this->connection->sender;

        return [
            'type' => 'connection',
            'title' => 'You are connected',
            'connection_id' => $this->connection->id,
            'user_id' => $otherUser->id,
            'message' => 'You and '.$otherUser->name.' are now connected 🎉',
        ];
    }
}
