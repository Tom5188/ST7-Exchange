<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsersPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view users');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view users');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create users');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update users');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete users');
    }
}
