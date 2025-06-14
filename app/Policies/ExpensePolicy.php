<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Ver se o usu�rio pode ver a despesa.
     */
    public function view(User $user, Expense $expense): bool
    {
        return $user->id === $expense->user_id;
    }

    /**
     * Ver se o usu�rio pode atualizar a despesa.
     */
    public function update(User $user, Expense $expense): bool
    {
        return $user->id === $expense->user_id;
    }

    /**
     * Ver se o usu�rio pode excluir a despesa.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return $user->id === $expense->user_id;
    }
}