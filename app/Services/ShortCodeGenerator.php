<?php

namespace App\Services;

use App\Models\ShortLink;
use Illuminate\Support\Str;

class ShortCodeGenerator
{
    public function generate(int $length = 6): string
    {
        do {
            $code = Str::random($length);
        } while (
            ShortLink::query()
                ->withTrashed()
                ->where('code', $code)
                ->exists()
        );

        return $code;
    }
}
