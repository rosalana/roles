<?php

namespace Rosalana\Roles\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Rosalana\Roles\Models\Role;
use Rosalana\Roles\Traits\HasRoles;
use Rosalana\Roles\Traits\Roleable;

class RolesManager
{
    /**
     * This is the model that has the roles assigned to it.
     */
    protected Model&HasRoles $assignee;

    /**
     * The model that this manager is operating on.
     */
    protected Model&Roleable $roleable;

    /**
     * Set the assignee for the roles.
     */
    public function for(Model&HasRoles $assignee): self
    {
        $this->assignee = $assignee;
        return $this;
    }

    /**
     * Set the model that this manager will operate on.
     */
    public function on(Model&Roleable $roleable): self
    {
        $this->roleable = $roleable;
        return $this;
    }

    public function assign(string|Role $role)
    {
        $role = $this->resolveRole($role);
        $this->ensureContext();

        $role ? $this->assignee->roles()->syncWithoutDetaching($role) : throw new \RuntimeException("Role not found: {$role}");
    }

    public function detach(string|Role $role)
    {
        $role = $this->resolveRole($role);
        $this->ensureContext();

        $role ? $this->assignee->roles()->detach($role) : throw new \RuntimeException("Role not found: {$role}");
    }

    public function has(string|Role $role): bool
    {
        $role = $this->resolveRole($role);
        $this->ensureContext();

        return $role ? $this->assignee->roles()->where('id', $role->id)->exists() : false;
    }

    public function get(): ?Role
    {
        $this->ensureContext();

        return $this->assignee->roles()
            ->where('roleable_type', get_class($this->roleable))
            ->where('roleable_id', $this->roleable->getKey())
            ->first();
    }

    public function can(string $permission): bool
    {
        $this->ensureContext();

        $role = $this->get();
        if (!$role) return false;

        $validPermissions = $this->roleable::permissions();
        $resolvedPermissions = $this->resolveWildcardPermissions($role->permissions);

        return in_array($permission, $resolvedPermissions) && in_array($permission, $validPermissions);
    }

    public function permissions(): Collection
    {
        $this->ensureContext();

        $role = $this->get();
        if (!$role) return collect();

        return collect($this->resolveWildcardPermissions($role->permissions));
    }

    public function resolveRole(string|Role $role): ?Role
    {
        return $role instanceof Role ? $role : $this->resolveRoleByName($role);
    }

    protected function resolveRoleByName(string $name): ?Role
    {
        $this->ensureContext();

        return $this->roleable->roles()->where('name', $name)->first();
    }

    protected function ensureContext(): void
    {
        if (!$this->roleable || !$this->assignee) {
            throw new \RuntimeException("Missing context: both 'roleable' and 'assignee' must be defined.");
        }
    }

    protected function resolveWildcardPermissions(array $permissions): array
    {
        if (in_array('*', $permissions)) {
            return $this->roleable::permissions();
        }

        return $permissions;
    }
}
