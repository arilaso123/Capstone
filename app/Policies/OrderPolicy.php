<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isOwner()) {
            return true;
        }

        if ($user->isCustomer() && $user->customer) {
            return $order->customer_id === $user->customer->id;
        }

        return false;
    }

    /**
     * Determine whether the customer can upload payment or request cancellation.
     */
    public function interact(User $user, Order $order): bool
    {
        if ($user->isCustomer() && $user->customer) {
            return $order->customer_id === $user->customer->id;
        }

        return false;
    }
}

