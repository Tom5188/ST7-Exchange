<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsersWalletOutPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view users-wallet-outs');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view users-wallet-outs');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create users-wallet-outs');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update users-wallet-outs');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete users-wallet-outs');
    }
}
