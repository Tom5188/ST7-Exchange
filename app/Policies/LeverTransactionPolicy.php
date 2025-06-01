<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeverTransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view lever-transactions');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view lever-transactions');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create lever-transactions');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update lever-transactions');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete lever-transactions');
    }
}
