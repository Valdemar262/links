<?php

namespace App\Policies;

use App\Models\ShortLink;
use App\Models\User;

class ShortLinkPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, ShortLink $shortLink): bool
    {
        return $user->id === $shortLink->user_id;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, ShortLink $shortLink): bool
    {
        return $user->id === $shortLink->user_id;
    }

    public function delete(User $user, ShortLink $shortLink): bool
    {
        return $user->id === $shortLink->user_id;
    }

    public function restore(User $user, ShortLink $shortLink): bool
    {
        return $user->id === $shortLink->user_id;
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
