<?php

namespace Tests\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Symfony\Component\Console\Command\ListCommand;

use function Pest\Laravel\artisan;

it('has a list command that produces no errors', function () {
    artisan(ListCommand::class)->assertExitCode(Command::SUCCESS);
});

it('displays crawl progress columns', function () {
    SiteSearchConfig::factory()->create([
        'urls_found' => 10,
        'urls_failed' => 2,
        'finish_reason' => 'completed',
    ]);

    Artisan::call('site-search:list');
    $output = Artisan::output();

    expect($output)
        ->toContain('URLs Found')
        ->toContain('Crawl Status')
        ->toContain('completed');
});
