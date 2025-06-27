<?php

namespace Rosalana\Roles\Providers;

use Rosalana\Core\Contracts\Package;

class Roles implements Package
{
    public function resolvePublished(): bool
    {
        return true;
    }

    public function publish(): array
    {
        return [
            'config' => [
                'label' => 'Publish configuration settings to rosalana.php',
                'run' => function () {
                    //
                }
            ],
            'migrations' => [
                'label' => 'Publish database migrations',
                'run' => function () {
                    //
                }
            ]
        ];
    }
}
