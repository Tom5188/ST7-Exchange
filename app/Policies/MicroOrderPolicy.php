<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MicroOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view micro-orders');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view micro-orders');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create micro-orders');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update micro-orders');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete micro-orders');
    }
}
