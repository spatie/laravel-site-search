<?php

namespace Spatie\SiteSearch\Exceptions;

use Exception;

class NoQuerySet extends Exception
{
    public static function make(): self
    {
        return new static('No query has been set');
    }
}
