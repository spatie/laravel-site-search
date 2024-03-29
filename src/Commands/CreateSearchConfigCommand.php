<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

use Spatie\SiteSearch\Models\SiteSearchConfig;

class CreateSearchConfigCommand extends Command
{
    protected $signature = 'site-search:create-index';

    public function handle()
    {
        intro("Let's create your index!");

        $name = text(
            label: 'What should your index be named?',
            placeholder: 'E.g. my-index',
            required: 'An index name is required.'
        );

        $url = text(
            label: 'Which url should be crawled to fill this index?',
            placeholder: 'E.g. https://example.com',
            required: 'A URL is required.',
            validate: function (string $value) {
                $passes = Validator::make(['url' => $value], [
                    'url' => 'url',
                ])->passes();

                return $passes ? null : 'You must enter a valid URL';
            }
        );

        SiteSearchConfig::create([
            'name' => $name,
            'crawl_url' => $url,
            'index_base_name' => $name,
            'enabled' => 1,
        ]);

        outro('Your index has been created.' . PHP_EOL . 'You should now run `php artisan site-search:crawl` to fill your index');
    }
}
