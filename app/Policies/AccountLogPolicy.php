<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view account-logs');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view account-logs');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create account-logs');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update account-logs');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete account-logs');
    }
}
