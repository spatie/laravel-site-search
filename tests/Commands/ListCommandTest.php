<?php

namespace Tests\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\ListCommand;
use function Pest\Laravel\artisan;

it('has a list command that produces no errors', function () {
    artisan(ListCommand::class)->assertExitCode(Command::SUCCESS);
});
