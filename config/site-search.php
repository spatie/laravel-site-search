<?php

return [
    'default_driver' =>  Spatie\SiteSearch\Drivers\MeiliSearchDriver::class,

    'default_profile' => Spatie\SiteSearch\Profiles\DefaultSearchProfile::class,

    'default_indexer' => Spatie\SiteSearch\Indexers\DefaultIndexer::class,

    'do_not_index' => [
        '[data-no-index]',
        'nav',
    ]
];
