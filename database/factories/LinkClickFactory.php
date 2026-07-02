<?php

namespace Database\Factories;

use App\Models\LinkClick;
use App\Models\ShortLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinkClick>
 */
class LinkClickFactory extends Factory
{
    public function definition(): array
    {
        return [
            'short_link_id' => ShortLink::factory(),
            'ip_address'    => fake()->ipv4(),
            'user_agent'    => fake()->userAgent(),
            'clicked_at'    => now(),
        ];
    }
}
