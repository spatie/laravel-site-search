<?php

namespace Spatie\SiteSearch\SearchResults;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Spatie\Macroable\Macroable;

class Hit
{
    use Macroable;

    public CarbonInterface $dateModified;

    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public string $text,
        public string $dateModifiedTimestamp,
        public array $extra
    ) {
        $this->dateModified = Carbon::createFromTimestamp($this->dateModifiedTimestamp);
    }

    public function __get(string $name): mixed
    {
        return $this->extra[$name] ?? null;
    }
}
