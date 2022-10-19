<?php

namespace Mha\LaravelModelWatch\Commands;

use Illuminate\Console\Command;

class LaravelModelWatchCommand extends Command
{
    public $signature = 'laravel-model-watch';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
