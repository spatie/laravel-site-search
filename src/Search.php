<?php

namespace Spatie\SiteSearch;

use Illuminate\Pagination\Paginator;
use Spatie\SiteSearch\Exceptions\NoQuerySet;
use Spatie\SiteSearch\SearchResults\SearchResults;

class Search
{
    protected ?string $query = null;
    protected ?int $limit = null;
    protected int $offset = 0;

    public static function onIndex(string $indexName)
    {
        $searchIndex = SiteSearch::index($indexName);

        return new static($searchIndex);
    }

    public function __construct(
        protected SiteSearch $siteSearch
    ) {
    }

    public function query(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function get(): SearchResults
    {
        $this->ensureQueryHasBeenSet();

        return $this->siteSearch->search($this->query, $this->limit);
    }

    public function paginate(int $pageSize = 20, string $pageName = 'page'): Paginator
    {
        $this->ensureQueryHasBeenSet();

        $pageNumber = Paginator::resolveCurrentPage($pageName);

        $offset = ($pageNumber - 1) * $pageSize;

        /*
         *  We search for one more result than requested. If there are
         *  $pageSize + 1 number of results, then the paginator, which uses
         *  the original $pageSize, will know that there is an extra page.
         */
        $realPageSize = $pageSize + 1;

        $searchResults = $this->siteSearch->search($this->query, $realPageSize, $offset);

        return new Paginator(
            $searchResults->hits,
            $pageSize,
            $pageNumber,
        );
    }

    protected function ensureQueryHasBeenSet(): void
    {
        if (! $this->query) {
            throw NoQuerySet::make();
        }
    }
}
