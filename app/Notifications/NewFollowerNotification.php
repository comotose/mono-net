<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $follower
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'follow',
            'title' => 'Новая подписка',
            'text' => $this->follower->name.' подписался(ась) на вас',
            'url' => route('profile.show', $this->follower),
            'follower_name' => $this->follower->name,
            'follower_id' => $this->follower->id,
        ];
    }
}
