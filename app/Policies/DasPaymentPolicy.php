<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DasPayment;

class DasPaymentPolicy
{
    public function view(User $user, DasPayment $payment): bool
    {
        return $user->id === $payment->user_id;
    }

    public function update(User $user, DasPayment $payment): bool
    {
        return $user->id === $payment->user_id;
    }

    public function delete(User $user, DasPayment $payment): bool
    {
        return $user->id === $payment->user_id;
    }
}