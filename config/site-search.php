<?php

use Spatie\SiteSearch\Drivers\MeiliSearchDriver;
use Spatie\SiteSearch\Profiles\DefaultSearchProfile;

return [
    'sites' => [
        'default' => [
            'url' => env('APP_URL'),
            'driver' => MeiliSearchDriver::class,
            'index_name' => 'default-site-search-index',
            'profile' => DefaultSearchProfile::class,
        ],
    ],
];
