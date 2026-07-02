<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\ShortLinks\LinkClickRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectShortLinkController extends Controller
{
    public function __construct(
        private readonly LinkClickRecorder $linkClickRecorder,
    ) {}

    public function __invoke(string $code, Request $request): RedirectResponse
    {
        /** @var ShortLink $shortLink */
        $shortLink = ShortLink::query()
            ->where('code', $code)
            ->firstOrFail();

        $this->linkClickRecorder->record(
            shortLink: $shortLink,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->away($shortLink->original_url);
    }
}
