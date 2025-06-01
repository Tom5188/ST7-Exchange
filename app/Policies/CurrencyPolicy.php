<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view currencies');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view currencies');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create currencies');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update currencies');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete currencies');
    }
}
