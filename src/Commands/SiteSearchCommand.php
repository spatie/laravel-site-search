<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;

class SiteSearchCommand extends Command
{
    public $signature = 'laravel-site-search';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
