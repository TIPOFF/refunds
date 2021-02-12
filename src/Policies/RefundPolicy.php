<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Tipoff\Refunds\Models\Refund;
use Tipoff\Support\Contracts\Models\UserInterface;

class RefundPolicy
{
    use HandlesAuthorization;

    public function viewAny(UserInterface $user): bool
    {
        return $user->hasPermissionTo('view refunds') ? true : false;
    }

    public function view(UserInterface $user, Refund $refund): bool
    {
        return $user->hasPermissionTo('view refunds') ? true : false;
    }

    public function create(UserInterface $user): bool
    {
        return $user->hasPermissionTo('request refunds') ? true : false;
    }

    public function update(UserInterface $user, Refund $refund): bool
    {
        return $user->hasPermissionTo('issue refunds') ? true : false;
    }

    public function delete(UserInterface $user, Refund $refund): bool
    {
        return false;
    }

    public function restore(UserInterface $user, Refund $refund): bool
    {
        return false;
    }

    public function forceDelete(UserInterface $user, Refund $refund): bool
    {
        return false;
    }
}
