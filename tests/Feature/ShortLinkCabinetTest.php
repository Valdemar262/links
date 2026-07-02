<?php

namespace Tests\Feature;

use App\Filament\Resources\ShortLinkResource\Pages\CreateShortLink;
use App\Filament\Resources\ShortLinkResource\Pages\ListShortLinks;
use App\Filament\Resources\ShortLinkResource\Pages\ViewShortLink;
use App\Filament\Resources\ShortLinkResource\RelationManagers\ClicksRelationManager;
use App\Models\LinkClick;
use App\Models\ShortLink;
use App\Models\User;
use App\Services\ShortCodeGenerator;
use App\Services\ShortLinks\ShortLinkCreator;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShortLinkCabinetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_short_link_from_cabinet(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateShortLink::class)
            ->fillForm([
                'original_url' => 'https://example.com/page',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(ShortLink::class, [
            'user_id'      => $user->id,
            'original_url' => 'https://example.com/page',
            'clicks_count' => 0,
        ]);
    }

    public function test_user_cannot_create_short_link_with_non_http_url(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateShortLink::class)
            ->fillForm([
                'original_url' => 'ftp://example.com/file',
            ])
            ->call('create')
            ->assertHasFormErrors(['original_url' => 'starts_with']);
    }

    public function test_user_sees_only_own_short_links_in_cabinet(): void
    {
        $user = User::factory()->create();
        $ownLink = ShortLink::factory()->for($user)->create();
        $otherUserLink = ShortLink::factory()->create();

        Livewire::actingAs($user)
            ->test(ListShortLinks::class)
            ->assertCanSeeTableRecords([$ownLink])
            ->assertCanNotSeeTableRecords([$otherUserLink]);
    }

    public function test_user_cannot_open_other_users_short_link_pages(): void
    {
        $user = User::factory()->create();
        $otherUserLink = ShortLink::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('filament.cabinet.resources.short-links.view', $otherUserLink))
            ->assertNotFound();

        $this
            ->actingAs($user)
            ->get(route('filament.cabinet.resources.short-links.edit', $otherUserLink))
            ->assertNotFound();
    }

    public function test_user_can_delete_own_short_link(): void
    {
        $user = User::factory()->create();
        $shortLink = ShortLink::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(ListShortLinks::class)
            ->callTableAction(DeleteAction::class, $shortLink);

        $this->assertSoftDeleted($shortLink);
    }

    public function test_user_can_see_click_statistics_for_short_link(): void
    {
        $user = User::factory()->create();
        $shortLink = ShortLink::factory()
            ->for($user)
            ->has(LinkClick::factory()->count(2), 'clicks')
            ->create(['clicks_count' => 2]);

        Livewire::actingAs($user)
            ->test(ListShortLinks::class)
            ->assertCanSeeTableRecords([$shortLink])
            ->assertTableColumnStateSet('clicks_count', 2, record: $shortLink);

        Livewire::actingAs($user)
            ->test(ClicksRelationManager::class, [
                'ownerRecord' => $shortLink,
                'pageClass' => ViewShortLink::class,
            ])
            ->assertCanSeeTableRecords($shortLink->clicks);
    }

    public function test_short_link_creation_retries_when_generated_code_collides_at_insert(): void
    {
        $user = User::factory()->create();

        ShortLink::factory()->create(['code' => 'abc123']);

        $this->app->instance(ShortCodeGenerator::class, new class extends ShortCodeGenerator
        {
            private int $calls = 0;

            public function generate(int $length = 6): string
            {
                return $this->calls++ === 0 ? 'abc123' : 'def456';
            }
        });

        $shortLink = app(ShortLinkCreator::class)->create($user, 'https://example.com/page');

        $this->assertSame('def456', $shortLink->code);
        $this->assertDatabaseHas(ShortLink::class, [
            'user_id'      => $user->id,
            'original_url' => 'https://example.com/page',
            'code'         => 'def456',
        ]);
    }
}
