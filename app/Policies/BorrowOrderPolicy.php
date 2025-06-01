<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BorrowOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view borrow-orders');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view borrow-orders');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create borrow-orders');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update borrow-orders');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete borrow-orders');
    }
}
