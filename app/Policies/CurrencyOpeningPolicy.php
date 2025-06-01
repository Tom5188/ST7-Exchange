<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyOpeningPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view currency-openings');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view currency-openings');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create currency-openings');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update currency-openings');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete currency-openings');
    }
}
