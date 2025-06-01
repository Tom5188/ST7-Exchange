<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BorrowPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view borrows');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view borrows');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create borrows');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update borrows');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete borrows');
    }
}
