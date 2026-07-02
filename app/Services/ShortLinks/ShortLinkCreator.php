<?php

namespace App\Services\ShortLinks;

use App\Models\ShortLink;
use App\Models\User;
use App\Services\ShortCodeGenerator;

readonly class ShortLinkCreator
{
    public function __construct(
        private ShortCodeGenerator $shortCodeGenerator,
    ) {}

    public function create(User $user, string $originalUrl): ShortLink
    {
        /** @var ShortLink */
        return ShortLink::query()->create([
            'user_id' => $user->id,
            'original_url' => $originalUrl,
            'code' => $this->shortCodeGenerator->generate(),
            'clicks_count' => 0,
        ]);
    }
}
