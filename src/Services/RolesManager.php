<?php

namespace Rosalana\Roles\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Rosalana\Roles\Traits\HasRoles;
use Rosalana\Roles\Traits\Roleable;

class RolesManager
{
    /**
     * The model that this manager is operating on.
     */
    protected $model;

    /**
     * This is the model that has the roles assigned to it.
     */
    protected $assignee;

    public function on(Model&Roleable $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function for(Model&HasRoles $assignee): self
    {
        $this->assignee = $assignee;
        return $this;
    }

    public function get(): Collection
    {
        $this->validateContext();

        return $this->assignee->roles()
            ->where('roleable_type', get_class($this->model))
            ->where('roleable_id', $this->model->getKey())
            ->get();
    }

    public function check(string $roleOrPermissionName): bool
    {
        $this->validateContext();

        // Check if the role or permission exists in the roles of the assignee
        return $this->assignee->roles()
            ->where('roleable_type', get_class($this->model))
            ->where('roleable_id', $this->model->getKey())
            ->where(function ($query) use ($roleOrPermissionName) {
                $query->where('name', $roleOrPermissionName)
                    ->orWhereJsonContains('permissions', $roleOrPermissionName);
            })
            ->exists();
    }

    public function assign(string $role)
    {
        $this->validateContext();
    }

    private function validateContext(): void
    {
        if (!$this->model instanceof Model || !$this->assignee instanceof Model) {
            throw new \InvalidArgumentException('RolesManager must be called on a model that implements Roleable and HasRoles traits.');
        }
    }
}
