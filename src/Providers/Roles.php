<?php

namespace Rosalana\Roles\Providers;

use Illuminate\Support\Facades\Artisan;
use Rosalana\Configure\Configure;
use Rosalana\Core\Contracts\Package;

class Roles implements Package
{
    public function resolvePublished(): bool
    {
        return Configure::fileExists('rosalana') && Configure::file('rosalana')->has('roles');
    }

    public function publish(): array
    {
        return [
            'config' => [
                'label' => 'Publish configuration settings to rosalana.php',
                'run' => function () {

                    Configure::file('rosalana')
                        ->section('roles')
                        ->withComment('Rosalana Roles Configuration', 'Configurate the roles and permissions for the application.')
                        ->value('enum', 'Rosalana\Roles\Enums\RoleEnum::class')
                        ->value('auto-migrate', 'true') // Automatically migrate permissions when find undefined permission on the model - needs permissionAlias -> not using yet
                        ->value('banned', '[\'banned\', \'unknown\']')
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
            ],
            'role_enum' => [
                'label' => 'Publish RoleEnum for customization',
                'run' => function () {
                    Artisan::call('vendor:publish', [
                        '--tag' => 'rosalana-roles-role-enum',
                        '--force' => true
                    ]);
                }
            ]
        ];
    }
}
