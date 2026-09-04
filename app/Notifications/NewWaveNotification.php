<?php

namespace App\Notifications;

use App\Models\Connection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewWaveNotification extends Notification
{
    use Queueable;

    public function __construct(public Connection $waveConnection) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wave',
            'title' => 'New wave',
            'connection_id' => $this->waveConnection->id,
            'sender_id' => $this->waveConnection->sender_id,
            'message' => $this->waveConnection->sender->name.' waved at you 👋',
        ];
    }
}
