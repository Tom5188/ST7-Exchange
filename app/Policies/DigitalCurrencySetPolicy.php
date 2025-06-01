<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DigitalCurrencySetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view digital-currency-sets');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view digital-currency-sets');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create digital-currency-sets');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update digital-currency-sets');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete digital-currency-sets');
    }
}
