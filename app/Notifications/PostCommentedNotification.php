<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PostCommentedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $actor,
        private readonly Post $post,
        private readonly Comment $comment
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
            'kind' => 'comment',
            'title' => 'Новый комментарий',
            'text' => $this->actor->name.' прокомментировал(а) вашу публикацию',
            'url' => route('profile.show', $this->actor),
            'actor_name' => $this->actor->name,
            'icon' => $this->actor->avatarUrl(),
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
        ];
    }
}
