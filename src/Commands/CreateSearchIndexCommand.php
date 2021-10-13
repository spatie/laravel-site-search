<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Spatie\SiteSearch\Models\SiteSearchIndex;

class CreateSearchIndexCommand extends Command
{
    protected $signature = 'search-index:create';

    public function handle()
    {
        $this->info('Let create your index!');

        $this->newLine();
        $name = $this->ask('What should your index be named?');

        $this->newLine();
        $url = $this->ask('Great! Which url should be crawled to fill this index?');

        SiteSearchIndex::create([
            'name' => $name,
            'crawl_url' => $url,
            'enabled' => 1,
        ]);

        $this->newLine();
        $this->info('Your index has been created.');
        $this->info('You should now run `php artisan search-index:crawl` to fill your index');
    }
}
