<?php

namespace Spatie\SiteSearch\Actions;

use Spatie\SiteSearch\Contracts\FindClosestAnchorAction;
use Symfony\Component\DomCrawler\Crawler;

class DefaultFindClosestAnchorAction implements FindClosestAnchorAction
{
    public function execute(string $html, int $textPosition): ?string
    {
        $crawler = new Crawler($html);
        
        // Get all headings with IDs
        $headings = $crawler->filter('h1[id], h2[id], h3[id], h4[id], h5[id], h6[id]');
        
        if ($headings->count() === 0) {
            return null;
        }
        
        // Extract text positions of each heading
        $headingPositions = [];
        $currentPosition = 0;
        
        // We need to iterate through all elements to find text positions
        $crawler->filter('body *')->each(function (Crawler $node) use (&$headingPositions, &$currentPosition) {
            $tagName = $node->nodeName();
            
            // Check if it's a heading with an ID
            if (in_array($tagName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                $id = $node->attr('id');
                if ($id) {
                    $headingPositions[] = [
                        'id' => $id,
                        'position' => $currentPosition,
                        'level' => (int) substr($tagName, 1),
                    ];
                }
            }
            
            // Add text length to current position
            $text = $node->text();
            $currentPosition += strlen($text);
        });
        
        if (empty($headingPositions)) {
            return null;
        }
        
        // Find the closest heading that comes before the text position
        $closestHeading = null;
        $closestDistance = PHP_INT_MAX;
        
        foreach ($headingPositions as $heading) {
            if ($heading['position'] <= $textPosition) {
                $distance = $textPosition - $heading['position'];
                // Prefer closer headings, but also prefer higher-level (lower number) headings at same distance
                if ($distance < $closestDistance || 
                    ($distance === $closestDistance && $closestHeading && $heading['level'] < $closestHeading['level'])) {
                    $closestDistance = $distance;
                    $closestHeading = $heading;
                }
            }
        }
        
        return $closestHeading ? $closestHeading['id'] : null;
    }
}
