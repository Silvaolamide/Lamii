<?php
namespace App\Notifications;
use App\Models\Connection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class NewWaveNotification extends Notification { use Queueable; public function __construct(public Connection $connection) {} public function via(object $notifiable): array { return ['database']; } public function toArray(object $notifiable): array { return ['type'=>'wave','connection_id'=>$this->connection->id,'sender_id'=>$this->connection->sender_id,'message'=>$this->connection->sender->name.' waved at you 👋']; } }
