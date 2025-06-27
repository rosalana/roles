<?php

namespace Rosalana\Roles\Providers;

use Illuminate\Support\Facades\Artisan;
use Rosalana\Core\Contracts\Package;
use Rosalana\Core\Support\Config;

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

                    Config::new('roles')
                        ->add('enum', 'Rosalana\\Roles\\Enums\\RoleEnum')
                        ->add('auto-migrate', 'true') // Automatically migrate permissions when find undefined permission on the model - needs permissionAlias
                        ->comment('Configurate the roles and permissions for the application.', 'Rosalana Roles Configuration')
                        ->save();
                }
            ],
            'migrations' => [
                'label' => 'Publish database migrations',
                'run' => function () {
                    Artisan::call('vendor:publish', [
                        '--tag' => 'rosalana-roles-migrations',
                        '--force' => true
                    ]);
                }
            ]
        ];
    }
}
