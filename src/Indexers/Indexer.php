<?php

namespace Spatie\SiteSearch\Indexers;

use Carbon\CarbonInterface;

interface Indexer
{
    public function pageTitle(): ?string;

    public function h1(): ?string;

    public function entries(): array;

    public function dateModified(): ?CarbonInterface;
}
