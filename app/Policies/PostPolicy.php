<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return (int) $post->author_user_id === (int) $user->id || (string) ($user->role ?? 'member') === 'admin';
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }

    public function moderate(User $user): bool
    {
        return (string) ($user->role ?? 'member') === 'admin';
    }
}
