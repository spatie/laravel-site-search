<?php

namespace Spatie\SiteSearch\Exceptions;

use Exception;

class SiteSearchIndexDoesNotExist extends Exception
{
    public static function make(string $name): self
    {
        return new self("There no search index named `{$name}`");
    }
}
