<?php

namespace Database\Factories;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShortLink>
 */
class ShortLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'original_url' => fake()->url(),
            'code'         => Str::random(6),
            'clicks_count' => 0,
        ];
    }
}
