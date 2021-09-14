<?php

namespace Spatie\SiteSearch\Indexers;

use Carbon\CarbonInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler;

class DefaultIndexer
{
    protected Crawler $domCrawler;

    public function __construct(
        protected UriInterface $url,
        protected ResponseInterface $response)
    {
        $html = (string)$this->response->getBody();

        $this->domCrawler = new Crawler($html);
    }

    public function title(): ?string
    {
        try {
            return $this->domCrawler->filter('title')->first()->text();
        } catch (Exception) {
            return null;
        }

    }

    public function entries(): array
    {
        try {
            $content = $this->domCrawler->filter('body')->first()->html();
        } catch (Exception) {
            return [];
        }

        $content = strip_tags($content);

        $entries =  array_map('trim', explode(PHP_EOL, $content));

        $entries = array_filter($entries);

        $entries = array_filter($entries, function (string $entry) {
            if (str_starts_with($entry, '/')) {
                return false;
            }

            if (str_starts_with($entry, '.')) {
                return false;
            }

            return strlen($entry) > 3;
        });

        return array_filter($entries);
    }

    public function dateModified(): ?CarbonInterface
    {
        return now();
    }
}
