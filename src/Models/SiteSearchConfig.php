<?php

namespace Spatie\SiteSearch\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Profiles\SearchProfile;

class SiteSearchConfig extends Model
{
    use HasFactory;

    public $guarded = [];

    public $casts = [
        'crawling_started_at' => 'datetime',
        'crawling_ended_at' => 'datetime',
        'extra' => 'array',
        'enabled' => 'boolean',
    ];

    public function scopeEnabled(Builder $query): void
    {
        $query->where('enabled', true);
    }

    public function generateAndUpdatePendingIndexName(): string
    {
        $pendingIndexName = $this->index_base_name . '-' . Str::random();

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

    public function getExtraValue(string $key, mixed $default = null)
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
