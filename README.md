# Rosalana Roles

This package is a part of the Rosalana eco-system. It provides a way to manage user roles and permissions across the Rosalana applications, allowing for a unified and consistent approach to user management.

> **Note:** This package is a extension of the [Rosalana Core](https://packagist.org/packages/rosalana/core) package.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
    - [Roleable Models](#roleable-models)
    - [HasRoles Models](#hasroles-models)
    - [Role Model](#role-model)
    - [Roles Manager](#roles-manager)
- [May Show in the Future](#may-show-in-the-future)
- [License](#license)

## Installation

To install the `rosalana/roles` package, you must first have the `rosalana/core` package installed. If you haven't installed it yet, please refer to the `rosalana/core` documentation.

After installing the `rosalana/core` package, use the `rosalana:add` command from the Rosalana CLI and select `rosalana/roles` from the list:

```bash
php artisan rosalana:add
```

After installing the package, you should publish its assets using the following command:

```bash
php artisan rosalana:publish
```

You can specify which files to publish. Publishing **the configuration files is required** to set up the package properly. Other files are optional and can be published as needed. However, it is recommended to publish all files to take full advantage of the package features.

## Configuration

After publishing the package, you will find a `rosalana.php` configuration file in the `config` directory of your Laravel application. You can customize these options according to your needs.

This file will grow over time as you add more Rosalana packages to your application. Each package contributes its own configuration section. The `rosalana.php` file serves as the central configuration hub for all Rosalana packages.

`rosalana/roles` package provides configuration options for:

-

## Features

### Roleable Models

The `rosalana/roles` package allows you to assign and manage roles for users within the context of specific Eloquent models — such as projects, teams, or workspaces.

Unlike traditional RBAC systems where roles are global, this package is designed for **context-aware role management**.
Each user can hold a different role depending on the model they are interacting with.

#### Defining Roleable Models

To start using roles, you simply add the `Roleable` trait to any model you want to make roleable:

```php
use Rosalana\Roles\Traits\Roleable;

class Workspace extends Model
{
    use Roleable;
}
```

The model will automatically gain methods to manage roles, permissions and users associated with it.

```php
$workspace->users(); // associated users
$workspace->roles(); // associated roles
$workspace->roleOf($user); // get the role of a user in this workspace
```

You can also manage roles and users directly:

```php
$workspace->join($user, 'admin');
$workspace->leave($user);
$workspace->newRole('editor', ['edit', 'view']);
$workspace->hasRole('editor');
```

#### Configuring Roleable Models

Each roleable model defines its **own role context**, making the roles clear, isolated, and easy to manage.

You can define the roleable models behavior with static methods in the model itself:

```php
use Rosalana\Roles\Traits\Roleable;

class Workspace extends Model
{
    use Roleable;

    public static function getUsersPivotTable(): string
    {
        return 'workspace_users'; // default is '{class_name}_users'
    }

    public static function permissions(): array
    {
        return ['view', 'edit', 'delete']; // default is []
    }

    /**
     * Define with roles should be created by new model instance.
     */
    public static function defaultRoles(): array
    {
        return [
            'default' => ['*'], // this is default
        ]
    }

    public static function defaultRole(): string
    {
        return 'default'; // default role name for new users
    }
}
```

### HasRoles Models

Models that should **receive roles** (typically `User`) must implement the `HasRoles` trait.

This trait gives your model the ability to **join or leave roleable models, query assigned roles**, and check for **permissions** in context.

#### Defining HasRoles Models

To make a model able to hold roles, simply add the `HasRoles` trait:

```php
use Rosalana\Roles\Traits\HasRoles;

class User extends Model
{
    use HasRoles;
}
```

This enables expressive methods like:

```php
$user->join($workspace, 'admin'); // Assign user to a workspace
$user->leave($workspace); // Remove user from the workspace
$user->roleIn($workspace); // Get the Role model instance
$user->hasRole('admin', $workspace); // Check role in context
```

You can also perform permission checks:

```php
$user->hasPermission('edit', $workspace); // Check if user can edit in this workspace
$user->hasAnyPermission(['view', 'edit'], $workspace); // Check if user has any of the permissions
```

The trait uses the internal `RolesManager` to handle role assignment and validation. All role actions are **strictly contextual** — they always require a target model.

### Role Model

The `Role` model represents a single role **within a specific roleable model**.

It contains:

- `name` – the role's unique name (e.g. `"admin"`)
- `permissions` – an array of allowed permissions

- `roleable_type` and `roleable_id` – the model this role belongs to

You usually don't create roles manually via `Role::create()`.
Instead, use the `newRole()` method from the roleable model:

```php
$workspace->newRole('editor', ['view', 'edit']);
```

You can also retrieve and manipulate roles directly:

```php
$role = $workspace->roles()->where('name', 'editor')->first();
$role->addPermission('publish');
$role->removePermission('delete');
$role->setPermissions(['view', 'edit']); // replaces all
```

The `Role` model performs validation:
It ensures all permissions assigned to the role exist in the roleable model's defined permissions.
If not, it throws an exception — preventing invalid permission states.

#### Permission Helpers

The `Role` model includes several permission helpers:

```php
$role->hasPermission('edit');                  // true / false
$role->hasPermissions(['view', 'edit']); // true / false
$role->hasAnyPermission(['view', 'delete']);   // true / false
$role->hasAllPermissions(['view', 'edit']); // true / false
```

These methods resolve wildcards (`*`) and aliases automatically using the configuration on the roleable model.

### Roles Manager

At the heart of the package is a powerful `RolesManager` class.
It provides the full API for assigning, retrieving, and validating roles between users and models.

The manager requires two things:

- The roleable model (e.g. `Project`)
- The user you want to check or assign

You can fluently define the context and run any role-related action:

```php
use Rosalana\Roles\Facades\Roles;

$manager = Roles::on($project)->for($user); 
// or Roles::context($project, $user);

$manager->assign('admin'); // assign 'admin' role to user in project
$manager->detach(); // leave the project
$manager->get(); // get the role instance
$manager->is('editor'); // true / false
$manager->can('edit'); // true / false
```
The `assign()` method automatically replaces the current role if the user is already assigned.
You don’t need to check if the user is a member — it just works.

## May Show in the Future

- **Permission in Gate**: Added permissions to Laravel's Gate system for more granular access control.
- **Auto-migration**: Automatically migrate database if deprecated permissions are detected.

- **Extrahovat config**: Config roleable modelu by mohl být v jiném souboru - {roleable}Config.php implements `RoleableConfigInterface`. Tím by to bylo nastavované zvlášť, ale stejně tak to může být v traitu, který si developer přidá sám. Takže nevím jestli to má smysl.

Stay tuned — we're actively shaping the foundation of the Rosalana ecosystem.

## Přidané 
- Automaticky najde všechny modely, které implementují `Roleable` trait. Hledají se runtime pokud app není v production modu. Jinak se cachují.

## License

Rosalana Roles is open-source under the [MIT license](/LICENCE), allowing you to freely use, modify, and distribute it with minimal restrictions.

You may not be able to use our systems but you can use our code to build your own.

For details on how to contribute or how the Rosalana ecosystem is maintained, please refer to each repository’s individual guidelines.

**Questions or feedback?**

Feel free to open an issue or contribute with a pull request. Happy coding with Rosalana!
