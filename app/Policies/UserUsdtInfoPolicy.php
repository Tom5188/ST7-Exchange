<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserUsdtInfoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view user-usdtinfo');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view user-usdtinfo');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create user-usdtinfo');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update user-usdtinfo');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete user-usdtinfo');
    }
}
