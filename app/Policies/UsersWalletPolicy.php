<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsersWalletPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view users-wallets');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view users-wallets');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create users-wallets');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update users-wallets');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete users-wallets');
    }
}
