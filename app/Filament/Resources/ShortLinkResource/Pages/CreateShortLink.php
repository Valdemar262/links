<?php

namespace App\Filament\Resources\ShortLinkResource\Pages;

use App\Filament\Resources\ShortLinkResource;
use App\Models\User;
use App\Services\ShortLinks\ShortLinkCreator;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShortLink extends CreateRecord
{
    protected static string $resource = ShortLinkResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var ShortLinkCreator $creator */
        $creator = app(ShortLinkCreator::class);

        /** @var User $user */
        $user = Filament::auth()->user();

        return $creator->create(
            user: $user,
            originalUrl: $data['original_url'],
        );
    }
}
