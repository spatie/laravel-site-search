<?php

namespace Spatie\SiteSearch\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Profiles\SearchProfile;

/**
 * @property int $id
 * @property string $name
 * @property string $crawl_url
 * @property string $index_base_name
 * @property bool $enabled
 * @property string|null $driver_class
 * @property string|null $profile_class
 * @property array|null $extra
 * @property string|null $index_name
 * @property int $number_of_urls_indexed
 * @property int $urls_found
 * @property int $urls_failed
 * @property string|null $finish_reason
 * @property string|null $pending_index_name
 * @property Carbon|null $crawling_started_at
 * @property Carbon|null $crawling_ended_at
 * @property-read int $document_count
 */
class SiteSearchConfig extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'crawling_started_at' => 'datetime',
            'crawling_ended_at' => 'datetime',
            'extra' => 'array',
            'enabled' => 'boolean',
        ];
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('enabled', true);
    }

    public function generateAndUpdatePendingIndexName(): string
    {
        $pendingIndexName = $this->index_base_name.'-'.Str::random();

        $this->update(['pending_index_name' => $pendingIndexName]);

        return $pendingIndexName;
    }

    public function getDriver(): Driver
    {
        /** @var \Spatie\SiteSearch\Drivers\Driver $driverClass */
        $driverClass = $this->driver_class ?? config('site-search.default_driver');

        return $driverClass::make($this);
    }

    public function getProfile(): SearchProfile
    {
        $profileClass = $this->profile_class ?? config('site-search.default_profile');

        return app($profileClass);
    }

    public function getExtraValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->extra, $key, $default);
    }

    public function getDocumentCountAttribute(): int
    {
        if (! $this->index_name) {
            return 0;
        }

        try {
            return $this->getDriver()->documentCount($this->index_name);
        } catch (Exception $exception) {
            return 0;
        }
    }
}
