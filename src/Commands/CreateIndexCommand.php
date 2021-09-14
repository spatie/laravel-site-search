<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use MeiliSearch\Client;
use Spatie\SiteSearch\Drivers\MeiliSearchDriver;

class CreateIndexCommand extends Command
{
    public $signature = 'site-search:create-index';

    public function handle()
    {
        $client = new Client('http://127.0.0.1:7700');

        $driver = new MeiliSearchDriver($client, 'my-index');

        $driver->createIndex();
    }
}
