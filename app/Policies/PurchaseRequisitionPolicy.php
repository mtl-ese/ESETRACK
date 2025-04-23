<?php
// app/Policies/purchaseRequisitionPolicy.php
namespace App\Policies;

use App\Models\purchaseRequisition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class purchaseRequisitionPolicy
{
    use HandlesAuthorization;

    // Allow all actions if the user is signed in
    public function before(User $user, $ability)
    {
        if ($user) {
            return true;
        }
    }

    public function view(User $user, purchaseRequisition $purchaseRequisition)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, purchaseRequisition $purchaseRequisition)
    {
        return true;
    }

}
