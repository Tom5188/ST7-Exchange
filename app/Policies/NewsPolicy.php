<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view news');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view news');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create news');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update news');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete news');
    }
}
