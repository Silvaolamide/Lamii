<?php

namespace App\Notifications;

use App\Models\Connection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConnectionAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public Connection $acceptedConnection) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $otherUser = $this->acceptedConnection->sender_id === $notifiable->id
            ? $this->acceptedConnection->recipient
            : $this->acceptedConnection->sender;

        return [
            'type' => 'connection',
            'title' => 'You are connected',
            'connection_id' => $this->acceptedConnection->id,
            'user_id' => $otherUser->id,
            'message' => 'You and '.$otherUser->name.' are now connected 🎉',
        ];
    }
}
