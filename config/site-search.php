<?php

return [
    'sites' => [
        'default' => [
            'url' => env('APP_URL'),
            'driver' => Spatie\SiteSearch\Drivers\MeiliSearchDriver::class,
            'index_name' => 'default-site-search-index',
            'profile' => Spatie\SiteSearch\Profiles\DefaultSearchProfile::class,
        ],
    ],
];
