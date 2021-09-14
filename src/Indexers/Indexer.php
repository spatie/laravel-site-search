<?php

namespace Spatie\SiteSearch\Indexers;

use Carbon\CarbonInterface;

interface Indexer
{
    public function title(): ?string;

    public function entries(): array;

    public function dateModified(): ?CarbonInterface;
}
