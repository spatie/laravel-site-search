<?php

namespace Spatie\SiteSearch\Exceptions;

use Exception;

class IndexNameNotSet extends Exception
{
    public static function make(): self
    {
        return new self('Tried to use index but no name was set');
    }
}
