<?php

use Spatie\SiteSearch\Profiles\DefaultSearchProfile;

return [
    'sites' => [
        'url' => '',
        'driver' => 'meilisearch',
        'index-name',
        'profile' => DefaultSearchProfile::class,
    ]
];
