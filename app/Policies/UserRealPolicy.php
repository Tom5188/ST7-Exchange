<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserRealPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view user-reals');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view user-reals');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create user-reals');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update user-reals');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete user-reals');
    }
}
