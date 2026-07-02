<?php

namespace App\Services\ShortLinks;

use App\Models\ShortLink;
use App\Models\User;
use App\Services\ShortCodeGenerator;
use Illuminate\Database\QueryException;

readonly class ShortLinkCreator
{
    private const int MAX_ATTEMPTS = 5;

    public function __construct(
        private ShortCodeGenerator $shortCodeGenerator,
    ) {}

    public function create(User $user, string $originalUrl): ShortLink
    {
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                /** @var ShortLink */
                return ShortLink::query()->create([
                    'user_id'      => $user->id,
                    'original_url' => $originalUrl,
                    'code'         => $this->shortCodeGenerator->generate(),
                    'clicks_count' => 0,
                ]);
            } catch (QueryException $exception) {
                if (! $this->isUniqueCodeViolation($exception) || $attempt === self::MAX_ATTEMPTS) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Unable to create a unique short link.');
    }

    private function isUniqueCodeViolation(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return in_array($exception->getCode(), ['23000', '23505'], true)
            && str_contains($message, 'short_links')
            && str_contains($message, 'code');
    }
}
