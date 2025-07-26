<?php

namespace Rosalana\Roles\Console\Commands;

use \Illuminate\Console\Command;
use Rosalana\Roles\Support\Config;

class CacheRoleable extends Command
{
    protected $signature = 'rosalana:cache-roleable';
    protected $description = 'Cache roleable models for faster access';

    public function handle()
    {
        $models = Config::all();

        $this->info('Cached ' . count($models) . ' roleable model(s):');
        
        foreach ($models as $class => $config) {
            $this->line("- {$class}");
        }
    }
}
