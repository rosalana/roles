<?php

namespace Rosalana\Roles\Console\Commands;

use Illuminate\Console\Command;

class ClearRoleable extends Command
{
    protected $signature = 'rosalana:clear-roleable';
    protected $description = 'Clear cached roleable models';

    public function handle()
    {
        cache()->forget('rosalana.roles.models');
        $this->info('Cleared roleable model cache.');
    }
}
