<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserLevelModelPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view user-levels');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view user-levels');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create user-levels');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update user-levels');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete user-levels');
    }
}
