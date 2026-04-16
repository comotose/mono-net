<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Message $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'message',
            'title' => 'Новое сообщение',
            'text' => 'Вам написал(а) '.$this->message->sender->name,
            'url' => route('messages.show', $this->message->sender),
            'sender_name' => $this->message->sender->name,
            'message_id' => $this->message->id,
        ];
    }
}
