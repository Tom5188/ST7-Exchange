<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChargeReqPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view charge-reqs');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view charge-reqs');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create charge-reqs');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update charge-reqs');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete charge-reqs');
    }
}
