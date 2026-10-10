<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;

class PostCommented extends TradeonNotification
{
    public function __construct(public Post $post, public User $actor) {}

    public function toArray(object $notifiable): array
    {
        return $this->payload('post_comment', 'New comment', $this->actor->name.' commented on your post.', '/feed');
    }
}
