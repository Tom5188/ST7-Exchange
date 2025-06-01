<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserSimulationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view user-simulations');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view user-simulations');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create user-simulations');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update user-simulations');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete user-simulations');
    }
}
