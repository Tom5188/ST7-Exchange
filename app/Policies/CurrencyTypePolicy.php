<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view currency-types');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view currency-types');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create currency-types');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update currency-types');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete currency-types');
    }
}
