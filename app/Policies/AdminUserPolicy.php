<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminUserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view admin-users');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view admin-users');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create admin-users');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update admin-users');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete admin-users');
    }
}
