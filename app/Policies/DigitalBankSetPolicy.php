<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DigitalBankSetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view digital-bank-sets');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view digital-bank-sets');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create digital-bank-sets');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update digital-bank-sets');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete digital-bank-sets');
    }
}
