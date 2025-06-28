<?php

namespace Rosalana\Roles\Support;

use Illuminate\Database\Eloquent\Model;

class Context
{
    /**
     * This is the model that has the roles assigned to it.
     */
    protected ?Model $assignee = null;
    /**
     * The model that this manager is operating on.
     */
    protected ?Model $roleable = null;

    public function activateRoleable(Model $roleable): self
    {
        $this->roleable = $roleable;
        return $this;
    }

    public function activateAssignee(Model $assignee): self
    {
        $this->assignee = $assignee;
        return $this;
    }

    

}