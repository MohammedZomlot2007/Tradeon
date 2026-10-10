<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;

class PostLiked extends TradeonNotification
{
    public function __construct(public Post $post, public User $actor) {}

    public function toArray(object $notifiable): array
    {
        return $this->payload('post_like', 'Your post was liked', $this->actor->name.' liked your post.', '/feed');
    }
}
