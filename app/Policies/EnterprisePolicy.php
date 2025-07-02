<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Enterprise;
use App\Models\User;

class EnterprisePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Enterprise $enterprise): bool
    {
        return $user->id === $enterprise->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Enterprise $enterprise): bool
    {
        return $user->id === $enterprise->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enterprise $enterprise): bool
    {
        return $user->id === $enterprise->user_id;
    }
}
