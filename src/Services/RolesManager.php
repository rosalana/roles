<?php

namespace Rosalana\Roles\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Rosalana\Roles\Models\Role;
use Rosalana\Roles\Support\Config;
use Rosalana\Roles\Support\Validator;
use Rosalana\Roles\Traits\HasRoles;
use Rosalana\Roles\Traits\Roleable;

class RolesManager
{
    /**
     * This is the model that has the roles assigned to it.
     */
    protected Model $assignee;

    /**
     * The model that this manager is operating on.
     */
    protected Model $roleable;

    /**
     * Set the assignee for the roles.
     */
    public function for(Model $assignee): self
    {
        $this->assignee = $assignee;
        return $this;
    }

    /**
     * Set the model that this manager will operate on.
     */
    public function on(Model $roleable): self
    {
        $this->roleable = $roleable;
        return $this;
    }

    /**
     * Set both the roleable model and the assignee.
     */
    public function context(Model $roleable, Model $assignee): self
    {
        $this->roleable = $roleable;
        $this->assignee = $assignee;
        return $this;
    }

    /**
     * Assign a role to the assignee on the roleable model.
     */
    public function assign(string|Role|null $role = null): void // pozor může být null!!!
    {
        $original = $role;
        $role = $this->resolveRole($role ?? Config::get($this->roleable::class)->get('default_role'));

        if (!$role) { // make custom exception later
            $name = is_string($original) ? $original : 'unknown';
            throw new \RuntimeException("Role '{$name}' not found or could not be resolved.");
        }

        $this->roleable->users()->syncWithoutDetaching([
            $this->assignee->getKey() => ['role_id' => $role->getKey()],
        ]);
    }

    /**
     * Detach the assignee from the roleable model.
     */
    public function detach(): void
    {
        $this->ensureContext();

        $this->roleable->users()->detach($this->assignee->getKey());
    }

    /**
     * Check if the assignee has a specific role on the roleable model.
     */
    public function is(string|Role $role): bool
    {
        $role = $this->resolveRole($role);
        $this->ensureContext();

        if (!$role) return false;

        return $this->get()?->getKey() === $role->getKey();
    }

    /**
     * Check if the assignee does not have a specific role on the roleable model.
     */
    public function isNot(string|Role $role): bool
    {
        return !$this->is($role);
    }

    /**
     * Get the role assigned to the assignee on the roleable model.
     */
    public function get(): ?Role
    {
        $this->ensureContext();

        $pivotTable = Config::get($this->roleable::class)->get('pivot_table');
        $roleableColumn = strtolower(class_basename($this->roleable)) . '_id';
        $assigneeColumn = strtolower(class_basename($this->assignee)) . '_id';

        $roleId = DB::table($pivotTable)
            ->where($assigneeColumn, $this->assignee->getKey()) // později na motph -teď ještě ne
            ->where($roleableColumn, $this->roleable->getKey())
            ->value('role_id');

        if (!$roleId) return null;

        $role = $this->roleable->roles()->find($roleId);

        if ($role) Validator::validatePermissions($this->roleable::class, collect($role->permissions));

        return $role ?: null;
    }

    /**
     * Get all permissions for the role assigned to the assignee on the roleable model.
     */
    public function permissions(): Collection
    {
        $this->ensureContext();

        $role = $this->get();
        if (!$role) return collect();

        return collect($this->resolvePermissions($role->permissions));
    }

    /**
     * Check if the assignee has a specific permission on the roleable model.
     */
    public function can(string $permission): bool
    {
        return $this->permissions()->contains($permission);
    }

    /**
     * Check if the assignee does not have a specific permission on the roleable model.
     */
    public function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    /**
     * Check if the assignee has any of the given permissions on the roleable model.
     */
    public function canAny(array $permissions): bool
    {
        return $this->permissions()->intersect($permissions)->isNotEmpty();
    }

    /**
     * Resolve the role from a string or Role instance.
     */
    protected function resolveRole(string|Role $role): ?Role
    {
        $this->ensureContext();

        if ($role instanceof Role) return $role;

        return $this->roleable->roles()->where('name', $role)->first();
    }

    /**
     * Resolve permissions for the given role.
     */
    protected function resolvePermissions(Role $role): array
    {
        $permissions = $role->permissions;

        Validator::validatePermissions($this->roleable::class, collect($permissions));

        if (empty($permissions)) return [];

        $permissions = $this->resolveWildcardPermissions($permissions);
        $permissions = $this->processPermissionsAlias($permissions);

        return array_values(array_unique($permissions));
    }

    /**
     * Resolve wildcard permissions.
     *
     * If '*' is present, return all permissions for the roleable model.
     */
    protected function resolveWildcardPermissions(array $permissions): array
    {
        if (in_array('*', $permissions)) {
            return $this->roleable::permissions();
        }

        return $permissions;
    }

    /**
     * Process permissions aliases.
     *
     * If an alias is defined for a permission, resolve it to the actual permissions.
     */
    protected function processPermissionsAlias(array $permissions): array
    {
        $alias = Config::get($this->roleable::class)->get('alias');

        if (empty($alias)) return $permissions;

        $resolvedPermissions = [];
        foreach ($permissions as $permission) {
            if (isset($alias[$permission])) {
                $resolvedPermissions = array_merge($resolvedPermissions, $alias[$permission]);
            } else {
                $resolvedPermissions[] = $permission;
            }
        }

        return array_unique($resolvedPermissions);
    }

    /**
     * Ensure that the context is set with both roleable and assignee.
     */
    protected function ensureContext(): void
    {
        if (!$this->roleable || !$this->assignee) {
            throw new \RuntimeException("Missing context: both 'roleable' and 'assignee' must be defined.");
        }
    }
}
