<?php

namespace App\Policies;

use App\Models\Income;
use App\Models\User;

class IncomePolicy
{
    /**
     * Ver se o usu�rio pode ver a despesa.
     */
    public function view(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }

    /**
     * Ver se o usu�rio pode atualizar a despesa.
     */
    public function update(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }

    /**
     * Ver se o usu�rio pode excluir a despesa.
     */
    public function delete(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }
}