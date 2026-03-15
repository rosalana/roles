# Rosalana Roles

Part of the [Rosalana ecosystem](https://github.com/rosalana). Provides two complementary role systems for Laravel applications:

- **Global Roles** — application-wide roles stored in context (e.g. `admin`, `moderator`, `user`). Managed by Rosalana Basecamp and synced on login.
- **Model Roles** — context-aware roles tied to specific Eloquent models (e.g. a user can be `owner` in one team and `viewer` in another).

Both systems are independent and can be used together.

> Requires [`rosalana/core`](https://github.com/rosalana/core) and [`rosalana/accounts`](https://github.com/rosalana/accounts).

---

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Global Roles](#global-roles)
  - [Role Enum](#role-enum)
  - [HasRoles Trait](#hasroles-trait)
  - [Global Role Middleware](#global-role-middleware)
- [Model Roles](#model-roles)
  - [Roleable Models](#roleable-models)
  - [Configuring a Roleable Model](#configuring-a-roleable-model)
  - [Model Role Methods on User](#model-role-methods-on-user)
  - [Roles Manager](#roles-manager)
  - [Role Model](#role-model)
  - [Model Role Middlewares](#model-role-middlewares)
- [Laravel Gate Integration](#laravel-gate-integration)
- [Suspended Users](#suspended-users)
- [Exceptions](#exceptions)
- [May Show in the Future](#may-show-in-the-future)
- [License](#license)

---

## Installation

Install via the Rosalana CLI:

```bash
php artisan rosalana:add
# select rosalana/roles
```

Then publish assets:

```bash
php artisan rosalana:publish
```

Publishing the **configuration** is required. Publishing the **Role enum** is recommended to customize global roles.

---

## Configuration

The `rosalana.php` config file (shared across Rosalana packages) accepts:

| Key | Description |
|-----|-------------|
| `roles.enum` | FQCN of your `RoleEnum` implementation. Defaults to the built-in `Rosalana\Roles\Enums\Roles`. |
| `roles.banned` | Array of role values treated as suspended (e.g. `['banned']`). Leave empty to disable auto-suspension. |

---

## Global Roles

Global roles are **application-wide**. A user has exactly one global role (e.g. `admin`, `user`, `banned`). They are sourced from Rosalana Basecamp and stored in the application context on login — there is no local database column.

### Role Enum

Global roles are defined as a PHP enum implementing the `RoleEnum` contract:

```php
interface RoleEnum
{
    public function level(): int;               // numeric hierarchy
    public function isAtLeast(self|string $role): bool;
    public function is(self|string $role): bool;
}
```

Publish and customize the default enum:

```bash
php artisan rosalana:publish
# choose rosalana-roles-role-enum
```

The published enum (`app/Enums/Roles.php`) ships with `ADMIN`, `MODERATOR`, `USER`, `BANNED`, and `UNKNOWN`. Each case has a numeric `level()` which drives `isAtLeast()` comparisons.

Register your custom enum in config:

```php
// config/rosalana.php
'roles' => [
    'enum' => \App\Enums\Roles::class,
],
```

### HasRoles Trait

Add `HasRoles` to your `User` model to enable global role access:

```php
use Rosalana\Roles\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

Available methods for global role checks:

```php
$user->role();              // returns RoleEnum|null from context
$user->isSuspended();       // true if role is in 'roles.banned' config
```

The role is automatically populated from Basecamp's response on `UserLogin`, `UserRefresh`, and `UserRegister` events.

### Global Role Middleware

Protect routes by requiring a minimum global role level:

```php
Route::middleware('role.is:admin')->group(function () {
    // only users with role level >= admin
});
```

The parameter is the enum case value (string), e.g. `admin`, `moderator`, `user`. Uses `isAtLeast()` — a `moderator` passes `role.is:user` but not `role.is:admin`.

Throws `InsufficientRoleException` (HTTP 403) on failure.

---

## Model Roles

Model roles are **context-aware**. A user holds a named role (e.g. `owner`, `editor`, `viewer`) within the scope of a specific model instance. Each role carries a set of permissions.

### Roleable Models

Add the `Roleable` trait to any model that should have role-managed members:

```php
use Rosalana\Roles\Traits\Roleable;

class Team extends Model
{
    use Roleable;
}
```

This gives the model:

```php
$team->users();              // BelongsToMany — users with a role in this team
$team->roles();              // MorphMany — all Role records for this team
$team->roleOf($user);        // ?Role — the user's current role
$team->join($user, 'owner'); // assign a role
$team->leave($user);         // remove the user
$team->newRole('editor', ['edit', 'view']); // create a new role definition
$team->hasRole('editor');    // true if the role exists on this model
$team->removeRole('editor'); // delete the role (detaches all users first)
```

When a `Roleable` model is **created**, default roles are seeded automatically. When **deleted**, all role records are cleaned up.

### Configuring a Roleable Model

Override static methods to customize the model's role behavior:

```php
class Team extends Model
{
    use Roleable;

    // Pivot table between teams and users (default: '{model}_users')
    public static function getUsersPivotTable(): string
    {
        return 'team_users';
    }

    // All valid permissions for this model
    public static function permissions(): array
    {
        return ['view', 'edit', 'delete', 'manage-members'];
    }

    // Permission aliases — map a shorthand to a group of permissions
    public static function permissionsAlias(): array
    {
        return [
            'editor' => ['view', 'edit'],
        ];
    }

    // Roles seeded when a new Team instance is created
    public static function defaultRoles(): array
    {
        return [
            'owner'  => ['*'],           // wildcard = all permissions
            'member' => ['view', 'edit'],
        ];
    }

    // Role assigned to a user when they join without an explicit role
    public static function defaultRole(): string
    {
        return 'member';
    }
}
```

### Model Role Methods on User

The `HasRoles` trait also provides model-scoped methods:

```php
$user->join($team, 'owner');            // assign user to team with role
$user->leave($team);                    // remove user from team
$user->roleIn($team);                   // ?Role — the Role model instance
$user->changeRole('editor', $team);     // update role

$user->hasRole('owner', $team);         // bool — exact role name match
$user->doesNotHaveRole('owner', $team); // bool

$user->hasPermission('edit', $team);              // bool
$user->doesNotHavePermission('delete', $team);    // bool
$user->hasAnyPermission(['edit', 'delete'], $team); // bool
```

### Roles Manager

The `Roles` facade exposes the `RolesManager` directly for low-level operations:

```php
use Rosalana\Roles\Facades\Roles;

$manager = Roles::on($team)->for($user);
// equivalent: Roles::context($team, $user)

$manager->assign('editor'); // assign or replace role
$manager->detach();         // remove from model
$manager->get();            // ?Role
$manager->is('editor');     // bool — exact match
$manager->isNot('owner');   // bool
$manager->can('edit');      // bool
$manager->cannot('delete'); // bool
$manager->canAny(['edit', 'delete']); // bool
$manager->permissions();    // Collection of permission strings
```

`assign()` is idempotent — it replaces the current role if one already exists.

### Role Model

A `Role` record represents a single named role scoped to one model instance. It stores:

- `name` — unique within the model instance (e.g. `owner`)
- `permissions` — JSON array of permission strings
- `roleable_type` / `roleable_id` — the owning model

Do not create roles via `Role::create()` directly — use `$model->newRole()` or let default roles be seeded automatically.

Direct permission management:

```php
$role->addPermission('publish');
$role->removePermission('delete');
$role->setPermissions(['view', 'edit']); // replaces all

$role->hasPermission('edit');
$role->hasAnyPermission(['view', 'delete']);
$role->hasAllPermissions(['view', 'edit']);
```

Permissions are validated against the model's `permissions()` array. Wildcards (`*`) expand to all available permissions. Aliases are resolved automatically.

### Model Role Middlewares

Protect routes that use [route model binding](https://laravel.com/docs/routing#route-model-binding):

```php
// Require exact role name in model bound to route parameter
Route::middleware('role.in:team,owner')->group(function () {
    // $team route param must have role 'owner' for the current user
});

// Require specific permission in model bound to route parameter
Route::middleware('permission.in:team,edit')->group(function () {
    // $team route param must grant 'edit' permission to the current user
});
```

Both middlewares expect the model to be resolved via Laravel's route model binding (e.g. `Route::get('/teams/{team}/settings', ...)`). The first argument is the route parameter name, the second is the role or permission name.

Both throw `InsufficientRoleException` (HTTP 403) on failure.

---

## Laravel Gate Integration

All permissions from every `Roleable` model are automatically registered with Laravel's Gate. This means you can use standard Laravel authorization anywhere:

```php
// Controller
$this->authorize('edit', $team);

// Blade
@can('edit', $team)

// Manual check
Gate::allows('edit', $team);
```

No manual policy registration is required. The package scans all models using the `Roleable` trait and registers each permission as a Gate ability.

In production, the model scan result is cached forever. Clear it after deploying changes to roles or permissions:

```bash
php artisan cache:clear
```

Or use the provided commands:

```bash
php artisan roles:cache   # warm the cache
php artisan roles:clear   # clear the cache
```

---

## Suspended Users

If a user's global role is listed in `roles.banned` config, they are treated as suspended. The `EnsureUserIsNotSuspended` middleware is automatically added to the `web` group and throws `AccountSuspendedException` (HTTP 403) on every request.

Handle the exception in `bootstrap/app.php` to log the user out:

```php
use Rosalana\Roles\Exceptions\AccountSuspendedException;

$exceptions->render(
    fn(AccountSuspendedException $e) => Accounts::logout()
);
```

---

## Exceptions

| Exception | Default status | Thrown by |
|-----------|---------------|-----------|
| `AccountSuspendedException` | 403 | `EnsureUserIsNotSuspended` middleware |
| `InsufficientRoleException` | 403 | `role.is`, `role.in`, `permission.in` middlewares |

Both extend `Symfony\Component\HttpKernel\Exception\HttpException` and can be caught and customized in your exception handler. The default message and HTTP status code can be overridden by catching the exception and returning a custom response.

## May Show in the Future

- **Auto-migration**: Automatically migrate database if deprecated permissions are detected.

Stay tuned — we're actively shaping the foundation of the Rosalana ecosystem.

## License

Rosalana Roles is open-source under the [MIT license](/LICENCE), allowing you to freely use, modify, and distribute it with minimal restrictions.

You may not be able to use our systems but you can use our code to build your own.

For details on how to contribute or how the Rosalana ecosystem is maintained, please refer to each repository’s individual guidelines.

**Questions or feedback?**

Feel free to open an issue or contribute with a pull request. Happy coding with Rosalana!
