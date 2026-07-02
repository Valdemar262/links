<?php

namespace App\Services\ShortLinks;

use App\Models\LinkClick;
use App\Models\ShortLink;
use Illuminate\Support\Facades\DB;

class LinkClickRecorder
{
    public function record(
        ShortLink $shortLink,
        string    $ipAddress,
        ?string   $userAgent,
    ): void {
        DB::transaction(function () use ($shortLink, $ipAddress, $userAgent): void {
            LinkClick::query()->create([
                'short_link_id' => $shortLink->id,
                'ip_address' => $ipAddress,
                'user_agent' => mb_substr((string) $userAgent, 0, 512),
                'clicked_at' => now(),
            ]);

            ShortLink::query()
                ->whereKey($shortLink->id)
                ->increment('clicks_count');
        });
    }
}
