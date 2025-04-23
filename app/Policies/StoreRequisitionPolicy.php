<?php

namespace App\Policies;

use App\Models\StoreRequisition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreRequisitionPolicy
{
    use HandlesAuthorization;

    // Allow all actions if the user is signed in
    public function before(User $user, $ability)
    {
        if ($user) {
            return true;
        }
    }

    public function view(User $user, StoreRequisition $storeRequisition)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, StoreRequisition $storeRequisition)
    {
        return true;
    }

    public function approve(User $user, StoreRequisition $storeRequisition)
    {
        return true;
    }
}

