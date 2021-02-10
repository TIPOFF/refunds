<?php

namespace Tipoff\Refunds\Policies;

use Tipoff\Refunds\Models\Refund;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefundPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view payments') ? true : false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function view(User $user, Refund $refund)
    {
        return $user->hasPermissionTo('view payments') ? true : false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('request refunds') ? true : false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function update(User $user, Refund $refund)
    {
        return $user->hasPermissionTo('issue refunds') ? true : false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function delete(User $user, Refund $refund)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function restore(User $user, Refund $refund)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function forceDelete(User $user, Refund $refund)
    {
        return false;
    }
}
