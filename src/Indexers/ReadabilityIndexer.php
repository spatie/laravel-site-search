<?php

namespace Spatie\SiteSearch\Indexers;

use andreskrey\Readability\Configuration;
use andreskrey\Readability\Readability;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class ReadabilityIndexer extends DefaultIndexer
{
    private Readability $readability;

    public function __construct(UriInterface $url, ResponseInterface $response)
    {
        parent::__construct($url, $response);

        $this->readability = new Readability(new Configuration());

        $html = (string)$this->response->getBody();

        $this->readability->parse($html);
    }

    public function pageTitle(): ?string
    {
        return $this->readability->getTitle();
    }

    public function entries(): array
    {
        $content = $this->readability->getContent();

        $entries = array_map('trim', explode(PHP_EOL, $content));

        $entries = array_filter($entries);

        $entries = array_filter($entries, function (string $entry) {
            if (str_starts_with($entry, '{"')) {
                return false;
            }

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
}
