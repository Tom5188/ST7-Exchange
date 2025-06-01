<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DigitalCurrencyAddressPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view digital-currency-addresses');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view digital-currency-addresses');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create digital-currency-addresses');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update digital-currency-addresses');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete digital-currency-addresses');
    }
}
