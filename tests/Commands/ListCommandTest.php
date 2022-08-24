<?php

namespace Tests\Commands;

use Illuminate\Console\Command;

use function Pest\Laravel\artisan;

use Symfony\Component\Console\Command\ListCommand;

it('has a list command that produces no errors', function () {
    artisan(ListCommand::class)->assertExitCode(Command::SUCCESS);
});
