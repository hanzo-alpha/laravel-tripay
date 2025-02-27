<?php

namespace HanzoAlpha\LaravelTripay\Commands;

use Illuminate\Console\Command;

class LaravelTripayCommand extends Command
{
    public $signature = 'laravel-tripay';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
