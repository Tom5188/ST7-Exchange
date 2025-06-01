<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WireTransferAccountPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view wire-transfer-accounts');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view wire-transfer-accounts');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create wire-transfer-accounts');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update wire-transfer-accounts');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete wire-transfer-accounts');
    }
}
