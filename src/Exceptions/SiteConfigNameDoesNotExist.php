<?php

namespace Spatie\SiteSearch\Exceptions;

use Exception;

class SiteConfigNameDoesNotExist extends Exception
{
    public static function make(string $configName): self
    {
        return new static("There is no site configured with the name `{$configName}`");
    }
}
