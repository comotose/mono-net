<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $follower
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    private function payload(): array
    {
        return [
            'kind' => 'follow',
            'title' => 'Новая подписка',
            'text' => $this->follower->name.' подписался(ась) на вас',
            'url' => route('profile.show', $this->follower),
            'follower_name' => $this->follower->name,
            'icon' => $this->follower->avatarUrl(),
            'follower_id' => $this->follower->id,
        ];
    }
}
