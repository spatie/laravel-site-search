<?php

namespace Spatie\SiteSearch\SearchResults;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Traits\Macroable;

class Hit
{
    use Macroable;

    public CarbonInterface $dateModified;

    public function __construct(
        public string $id,
        public string $title,
        public string $description,
        public string $highlightedDescription,
        public string $url,
        public string $entry,
        public string $highlightedEntry,
        public string $dateModifiedTimestamp,
        public array $extra
    ) {
        $this->dateModified = Carbon::createFromTimestamp($this->dateModifiedTimestamp);
    }

    public function __get(string $name): mixed
    {
        return $this->extra[$name] ?? null;
    }

    public function snippet(): ?string
    {
        return $this->shouldUseDescription()
            ? $this->description
            : $this->entry;
    }

    public function highlightedSnippet(): ?string
    {
        return $this->shouldUseDescription()
            ? $this->highlightedDescription
            : $this->highlightedEntry;
    }

    protected function shouldUseDescription(): bool
    {
        if (strlen($this->description) === 0) {
            return false;
        }

        if (strlen($this->entry) > 30) {
            return false;
        }

        return true;
    }
}
