<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyFloatingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view currency-floatings');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view currency-floatings');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create currency-floatings');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update currency-floatings');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete currency-floatings');
    }
}
