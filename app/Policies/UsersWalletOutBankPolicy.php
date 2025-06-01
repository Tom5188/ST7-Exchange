<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsersWalletOutBankPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view users-wallet-out-banks');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view users-wallet-out-banks');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create users-wallet-out-banks');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update users-wallet-out-banks');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete users-wallet-out-banks');
    }
}
