<?php

namespace Spatie\SiteSearch\Indexers;

use Carbon\CarbonInterface;
use DOMElement;
use DOMNodeList;
use Spatie\Crawler\CrawlResponse;
use Symfony\Component\DomCrawler\Crawler;

class DefaultIndexer implements Indexer
{
    protected Crawler $domCrawler;
    protected ?string $currentAnchor = null;
    protected array $entries = [];

    public function __construct(
        protected string $url,
        protected CrawlResponse $response
    ) {
        $html = $this->response->body();

        $this->domCrawler = new Crawler($html);
    }

    public function pageTitle(): ?string
    {
        return attempt(fn () => $this->domCrawler->filter('title')->first()->text());
    }

    public function h1(): ?string
    {
        return attempt(fn () => strip_tags($this->domCrawler->filter('h1')->first()->text()));
    }

    public function description(): ?string
    {
        $description = attempt(fn () => $this->domCrawler->filterXPath("//meta[@name='description']")->attr('content'));

        if ($description === null) {
            return null;
        }

        return preg_replace('/\s+/', ' ', $description);
    }

    public function entries(): array
    {
        $this->entries = [];
        $this->currentAnchor = null;

        $content = $this->getHtmlToIndex();

        if (is_null($content)) {
            return [];
        }

        // Create a new crawler with the cleaned body HTML
        $crawler = new Crawler($content);
        $body = $crawler->filter('body');

        if ($body->count() > 0) {
            $bodyNode = $body->getNode(0);
            if ($bodyNode && $bodyNode->hasChildNodes()) {
                $this->walkNodes($bodyNode->childNodes);
            }
        }

        return $this->filterEntries($this->entries);
    }

    /**
     * Recursively walk through DOM nodes and extract text with anchors
     */
    protected function walkNodes(DOMNodeList $nodes): void
    {
        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                if ($node->nodeType === XML_TEXT_NODE) {
                    $text = trim($node->nodeValue);
                    if (! empty($text)) {
                        $this->addTextEntry($text);
                    }
                }

                continue;
            }

            $tagName = strtolower($node->nodeName);

            if (in_array($tagName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                $id = $node->getAttribute('id');
                $this->currentAnchor = ! empty($id) ? $id : null;
            }

            if ($node->hasChildNodes()) {
                $this->walkNodes($node->childNodes);
            }
        }
    }

    /**
     * Add text entry, splitting by newlines
     */
    protected function addTextEntry(string $text): void
    {
        $lines = array_map('trim', explode(PHP_EOL, $text));

        foreach ($lines as $line) {
            if (! empty($line)) {
                $this->entries[] = [
                    'text' => $line,
                    'anchor' => $this->currentAnchor,
                ];
            }
        }
    }

    /**
     * Filter out unwanted entries
     */
    protected function filterEntries(array $entries): array
    {
        $filtered = array_filter($entries, function (array $entry) {
            $text = $entry['text'];

            if (str_starts_with($text, '{"')) {
                return false;
            }

            if (preg_match('#^[./][\w./\\\\-]#', $text)) {
                return false;
            }

            return strlen($text) > 3;
        });

        return array_values($filtered);
    }

    public function extra(): array
    {
        return [];
    }

    protected function getHtmlToIndex(): ?string
    {
        return attempt(function () {
            $this->removeIgnoredContent($this->domCrawler);

            return $this->domCrawler->filter("body")->html();
        });
    }

    protected function removeIgnoredContent(Crawler $crawler): Crawler
    {
        foreach (config('site-search.ignore_content_by_css_selector') as $selector) {
            $this->domCrawler
                ->filter($selector)
                ->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
        }

        return $crawler;
    }

    public function dateModified(): ?CarbonInterface
    {
        return now();
    }

    public function url(): string
    {
        return $this->url;
    }
}
