<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyMatchPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view currency-matches');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view currency-matches');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create currency-matches');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update currency-matches');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete currency-matches');
    }
}
