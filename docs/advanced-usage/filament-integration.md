---
title: Building a Filament integration
weight: 7
---

This package does not ship with a Filament integration, but it's straightforward to build one yourself. Below is a complete example of a Filament resource that lets you browse your search indexes and test queries from the admin panel.

## The resource class

Create a `SiteSearchConfigResource` that provides a read-only overview of all configured search indexes.

```php
namespace App\Filament\Resources;

use Exception;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\SiteSearch\Models\SiteSearchConfig;

class SiteSearchConfigResource extends Resource
{
    protected static ?string $model = SiteSearchConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Site Search';

    protected static ?string $slug = 'site-search';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('crawl_url')
                    ->label('Crawl URL')
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(fn (SiteSearchConfig $record) => static::getStatus($record))
                    ->color(fn (string $state) => match ($state) {
                        'OK' => 'success',
                        'Crawling...' => 'info',
                        'Processing...' => 'warning',
                        'Waiting on first crawl' => 'warning',
                        'Did not find index' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('document_count')
                    ->label('Documents'),
                Tables\Columns\TextColumn::make('number_of_urls_indexed')
                    ->label('URLs indexed'),
                Tables\Columns\TextColumn::make('urls_failed')
                    ->label('Failed'),
                Tables\Columns\TextColumn::make('crawling_ended_at')
                    ->label('Last crawl')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getStatus(SiteSearchConfig $config): string
    {
        if (! $config->index_name) {
            return 'Waiting on first crawl';
        }

        if ($config->pending_index_name) {
            return 'Crawling...';
        }

        try {
            if ($config->getDriver()->isProcessing($config->index_name)) {
                return 'Processing...';
            }
        } catch (Exception) {
            return 'Did not find index';
        }

        return 'OK';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteSearchConfigs::route('/'),
            'view' => Pages\ViewSiteSearchConfig::route('/{record}'),
        ];
    }
}
```

## The list page

The list page is a standard Filament `ListRecords` page.

```php
namespace App\Filament\Resources\SiteSearchConfigResource\Pages;

use App\Filament\Resources\SiteSearchConfigResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteSearchConfigs extends ListRecords
{
    protected static string $resource = SiteSearchConfigResource::class;
}
```

## The view page

The view page shows detailed information about a search index and includes a search form to test queries.

```php
namespace App\Filament\Resources\SiteSearchConfigResource\Pages;

use App\Filament\Resources\SiteSearchConfigResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Url;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchResults\SearchResults;

class ViewSiteSearchConfig extends ViewRecord
{
    protected static string $resource = SiteSearchConfigResource::class;

    protected static string $view = 'filament.resources.site-search-config.view';

    #[Url]
    public string $query = '';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('General')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('crawl_url')->label('Crawl URL'),
                        TextEntry::make('index_name')->label('Index name'),
                        TextEntry::make('status')
                            ->badge()
                            ->getStateUsing(fn (SiteSearchConfig $record) => SiteSearchConfigResource::getStatus($record)),
                        TextEntry::make('driver_class')
                            ->label('Driver')
                            ->default('Default'),
                        TextEntry::make('profile_class')
                            ->label('Profile')
                            ->default('Default'),
                    ]),
                Section::make('Crawl statistics')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('document_count')->label('Documents'),
                        TextEntry::make('number_of_urls_indexed')->label('URLs indexed'),
                        TextEntry::make('urls_found')->label('URLs found'),
                        TextEntry::make('urls_failed')->label('URLs failed'),
                        TextEntry::make('finish_reason')->label('Finish reason'),
                        TextEntry::make('crawling_started_at')->label('Crawl started')->dateTime(),
                        TextEntry::make('crawling_ended_at')->label('Crawl ended')->dateTime(),
                    ]),
            ]);
    }

    public function getSearchResults(): ?SearchResults
    {
        if (blank($this->query)) {
            return null;
        }

        $config = $this->record;

        if (! $config->index_name) {
            return null;
        }

        return $config->getDriver()->search($config->index_name, $this->query);
    }
}
```

## The view template

Create a Blade view at `resources/views/filament/resources/site-search-config/view.blade.php`.

```blade
<x-filament-panels::page>
    {{ $this->infolistAction }}

    <x-filament::section heading="Search">
        <div class="space-y-4">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="query"
                    placeholder="Search this index..."
                />
            </x-filament::input.wrapper>

            @if ($searchResults = $this->getSearchResults())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $searchResults->totalCount }} results in {{ $searchResults->processingTimeInMs }}ms
                </p>

                <div class="space-y-3">
                    @foreach ($searchResults->hits as $hit)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <a
                                href="{{ $hit->urlWithAnchor() }}"
                                target="_blank"
                                class="font-medium text-primary-600 hover:underline dark:text-primary-400"
                            >
                                {{ $hit->title() }}
                            </a>

                            @if ($hit->snippet())
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    {!! $hit->highlightedSnippet() ?? e($hit->snippet()) !!}
                                </p>
                            @endif

                            <p class="mt-1 text-xs text-gray-400">
                                {{ $hit->url }}
                                @if ($hit->dateModified())
                                    &middot; {{ $hit->dateModified()->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            @elseif (filled($this->query))
                <p class="text-sm text-gray-500 dark:text-gray-400">No results found.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
```

These examples give you a working starting point. You can customize the columns, layout, and search result rendering to fit your application.
