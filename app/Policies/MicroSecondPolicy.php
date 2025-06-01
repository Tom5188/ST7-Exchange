<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MicroSecondPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view micro-seconds');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view micro-seconds');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create micro-seconds');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update micro-seconds');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete micro-seconds');
    }
}
