<?php

namespace JoshEmbling\CacheMachine\Commands;

use Illuminate\Console\Command;

class CacheMachineCommand extends Command
{
    public $signature = 'cache-machine';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
