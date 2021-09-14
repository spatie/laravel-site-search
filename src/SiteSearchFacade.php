<?php

namespace Spatie\SiteSearch;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\SiteSearch\SiteSearch
 */
class SiteSearchFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-site-search';
    }
}
