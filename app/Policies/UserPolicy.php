<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $user, User $target): bool
    {
        return (int) $user->id === (int) $target->id || (string) ($user->role ?? 'user') === 'admin';
    }

    public function manageAdmin(User $user): bool
    {
        return (string) ($user->role ?? 'user') === 'admin';
    }
}
