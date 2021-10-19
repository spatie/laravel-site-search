<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Spatie\SiteSearch\Models\SiteSearchConfig;

class CreateSearchConfigCommand extends Command
{
    protected $signature = 'site-search:create-index';

    public function handle()
    {
        $this->info("Let's create your index!");

        $this->newLine();
        $name = $this->ask('What should your index be named?');

        $this->newLine();
        $url = $this->ask('Great! Which url should be crawled to fill this index?');

        SiteSearchConfig::create([
            'name' => $name,
            'crawl_url' => $url,
            'index_base_name' => $name,
            'enabled' => 1,
        ]);

        $this->newLine();
        $this->info('Your index has been created.');
        $this->info('You should now run `php artisan site-search:crawl` to fill your index');
    }
}
