<?php

namespace Spatie\SiteSearch\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Profiles\SearchProfile;

class SiteSearchIndex extends Model
{
    public $casts = [
        'crawling_started_at' => 'datetime',
        'crawling_ended_at' => 'datetime',
        'extra' => 'array',
        'enabled' => 'boolean',
    ];

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
}
