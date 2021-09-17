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
        public string $pageTitle,
        public string $h1,
        public string $highlightedH1,
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

    public function title()
    {
        return $this->pageTitle ?? $this->h1;
    }

    public function snippet(): ?string
    {
        $propertyName =  $this->getSnippetProperty();

        return $this->$propertyName;
    }

    public function highlightedSnippet(): ?string
    {
        $propertyName = $this->getSnippetProperty();

        ray($propertyName);
        $propertyName = ucfirst($propertyName);
        ray($propertyName)->blue();
        $propertyName = 'highlighted' . $propertyName;

        return $this->$propertyName;
    }

    protected function getSnippetProperty(): string
    {
        $propertyName = collect([
            'entry' => $this->entry,
            'description' => $this->description,
            'h1' => $this->h1,
        ])
            ->filter(fn(?string $value) => strlen($value) > 0)
            ->sortBy(fn(?string $value) => strlen($value))
            ->reverse()
            ->keys()
            ->first();

        return $propertyName ?? 'entry';
    }
}
