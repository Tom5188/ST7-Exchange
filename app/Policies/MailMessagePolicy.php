<?php

namespace App\Policies;

use App\Models\AdminUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MailMessagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view messages');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view messages');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create messages');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('update messages');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete messages');
    }
}
