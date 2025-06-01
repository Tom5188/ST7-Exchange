<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeverMultiplePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view lever-multiples');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view lever-multiples');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create lever-multiples');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update lever-multiples');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete lever-multiples');
    }
}
