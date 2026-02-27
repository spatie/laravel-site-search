<?php

namespace Spatie\SiteSearch\Contracts;

interface FindClosestAnchorAction
{
    /**
     * Find the closest heading anchor for a given position in HTML content.
     *
     * @param string $html The HTML content to search within
     * @param int $textPosition The character position in the text to find anchor for
     * @return string|null The anchor ID (without # prefix) or null if no heading found
     */
    public function execute(string $html, int $textPosition): ?string;
}
