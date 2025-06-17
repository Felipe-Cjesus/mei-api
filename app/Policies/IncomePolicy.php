<?php

namespace App\Policies;

use App\Models\Income;
use App\Models\User;

class IncomePolicy
{
    /**
     * Ver se o usuário pode ver a despesa.
     */
    public function view(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }

    /**
     * Ver se o usuário pode atualizar a despesa.
     */
    public function update(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }

    /**
     * Ver se o usuário pode excluir a despesa.
     */
    public function delete(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }
}