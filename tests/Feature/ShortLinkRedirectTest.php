<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortLinkRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_link_redirects_to_original_url_and_saves_click(): void
    {
        $user = User::factory()->create();

        $shortLink = ShortLink::query()->create([
            'user_id' => $user->id,
            'original_url' => 'https://example.com/page',
            'code' => 'abc123',
        ]);

        $response = $this->get('/abc123');

        $response->assertRedirect('https://example.com/page');

        $this->assertDatabaseHas('link_clicks', [
            'short_link_id' => $shortLink->id,
        ]);

        $this->assertSame(1, $shortLink->fresh()->clicks_count);
    }

    public function test_unknown_short_link_returns_404(): void
    {
        $this->get('/qwerty')->assertNotFound();
    }
}
