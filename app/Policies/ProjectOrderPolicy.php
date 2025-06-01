<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view project-orders');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view project-orders');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create project-orders');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update project-orders');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete project-orders');
    }
}
