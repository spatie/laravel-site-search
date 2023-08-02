<?php

use Illuminate\Console\Command;

use function Pest\Laravel\artisan;

use Spatie\SiteSearch\Commands\CreateSearchConfigCommand;

it('has a command to create a site search config', function () {
    artisan(CreateSearchConfigCommand::class)
       ->expectsQuestion('What should your index be named?', 'test-index')
       ->expectsQuestion('Great! Which url should be crawled to fill this index?', 'https://example.com')
       ->assertExitCode(Command::SUCCESS);

    $this->assertDatabaseHas('site_search_configs', [
        'name' => 'test-index',
        'crawl_url' => 'https://example.com',
        'index_base_name' => 'test-index',
        'enabled' => 1,
    ]);
})->todo('Fix this tests when Laravel Prompts are testable');
