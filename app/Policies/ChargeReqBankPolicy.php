<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChargeReqBankPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view charge-req-banks');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view charge-req-banks');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create charge-req-banks');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update charge-req-banks');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete charge-req-banks');
    }
}
