<?php

namespace Spatie\SiteSearch\Indexers;

use Carbon\CarbonInterface;

interface Indexer
{
    /*
     * The page title that should be put in the search index.
     */
    public function pageTitle(): ?string;

    /*
     * The H1 that should be put in the search index.
     */
    public function h1(): ?string;

    /*
     * We can index all html of page directly, as most search engines have
     * a small limit on how long a search entry should be.
     *
     * This function should return the content of the response chopped up in
     * little pieces of text of a few sentences long.
     *
     * Each entry should be an array with 'text' and optionally 'anchor' keys:
     * [
     *     ['text' => 'Introduction text...', 'anchor' => 'intro'],
     *     ['text' => 'Installation guide...', 'anchor' => 'installation'],
     * ]
     */
    public function entries(): array;

    /*
     * This function should return the date when the content
     * was modified for the last time.
     */
    public function dateModified(): ?CarbonInterface;

    /*
     * The meta description that should be put in the search index.
     */
    public function description(): ?string;

    /*
     * Any keys and values this function returns will also
     * be put in the search index. This is useful for adding
     * custom attributes.
     *
     * More info: https://spatie.be/docs/laravel-site-search/v1/advanced-usage/indexing-extra-properties
     */
    public function extra(): array;

    /*
     * This function should return the url of the page.
     */
    public function url(): string;
}
