<?php

namespace Tests\TestSupport\TestClasses\Indexers;

use Spatie\SiteSearch\Indexers\DefaultIndexer;

class IndexerWithExtraInfo extends DefaultIndexer
{
    public function extra(): array
    {
        return [
            'extraName' => 'extraValue',
        ];
    }
}
