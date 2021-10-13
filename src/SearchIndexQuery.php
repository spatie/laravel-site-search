<?php

namespace Spatie\SiteSearch;

use Illuminate\Pagination\Paginator;
use Spatie\SiteSearch\Exceptions\NoQuerySet;
use Spatie\SiteSearch\SearchResults\SearchResults;

class SearchIndexQuery
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
    )
    {

    }

    public function search(string $query): self
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

        $offset = ($pageNumber -1 ) * $pageSize;

        $searchResults = $this->siteSearch->search($this->query, $pageSize, $offset);

       return new Paginator($searchResults->hits, $pageSize);
    }

    protected function ensureQueryHasBeenSet(): void
    {
        if (! $this->query) {
            throw NoQuerySet::make();
        }
    }
}
