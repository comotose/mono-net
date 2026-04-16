<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostLikedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $actor,
        private readonly Post $post
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'like',
            'title' => 'Новый лайк',
            'text' => $this->actor->name.' оценил(а) вашу публикацию',
            'url' => route('profile.show', $this->actor),
            'actor_name' => $this->actor->name,
            'post_id' => $this->post->id,
        ];
    }
}
