<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WireTransferCurrencyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view wire-transfer-currencies');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view wire-transfer-currencies');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create wire-transfer-currencies');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update wire-transfer-currencies');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete wire-transfer-currencies');
    }
}
