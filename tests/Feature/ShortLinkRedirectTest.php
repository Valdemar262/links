<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortLinkRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_link_redirects_to_original_url_and_saves_click(): void
    {
        $shortLink = ShortLink::factory()->create([
            'original_url' => 'https://example.com/page',
            'code' => 'abc123',
        ]);

        $response = $this
            ->withHeader('User-Agent', 'Feature Test Browser')
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get('/abc123');

        $response->assertRedirect('https://example.com/page');

        $this->assertDatabaseHas('link_clicks', [
            'short_link_id' => $shortLink->id,
            'ip_address'    => '203.0.113.10',
            'user_agent'    => 'Feature Test Browser',
        ]);

        $this->assertSame(1, $shortLink->fresh()->clicks_count);
    }

    public function test_unknown_short_link_returns_404(): void
    {
        $this->get('/qwerty')->assertNotFound();
    }
}
