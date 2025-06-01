<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view projects');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view projects');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create projects');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update projects');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete projects');
    }
}
