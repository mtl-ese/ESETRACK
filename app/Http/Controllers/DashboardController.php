<?php

namespace App\Http\Controllers;

use App\Models\Acquired;
use App\Models\EmergencyRequisition;
use App\Models\PurchaseRequisition;
use App\Models\RecoveryStoreRequisition;
use App\Models\StoreItem;
use App\Models\StoreRequisition;
use App\Models\StoreReturn;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function create()
    {
        $totalPurchaseRequisitions = PurchaseRequisition::count();
        $totalStoreRequisitions = StoreRequisition::count();
        $totalRecoveryRequisitions = RecoveryStoreRequisition::count();
        $totalReturns = StoreReturn::count();
        $totalEmergencies = EmergencyRequisition::count();
        $totalAcquired = Acquired::count();

        //get users
        $users = User::with('onlineTimes')->get();

        $lastPurchaseUpdated = PurchaseRequisition::latest('updated_at')->first();
        $lastStoreUpdated = StoreRequisition::latest('updated_at')->first();
        $lastRecoveryUpdated = RecoveryStoreRequisition::latest('updated_at')->first();
        $lastReturnsUpdated = StoreReturn::latest('updated_at')->first();
        $lastEmergenciesUpdated = EmergencyRequisition::latest('updated_at')->first();
        $lastAcquiredUpdated = Acquired::latest('updated_at')->first();



        return view('dashboard', [
            'purchase' => $totalPurchaseRequisitions,
            'store' => $totalStoreRequisitions,
            'recovery' => $totalRecoveryRequisitions,
            'return' => $totalReturns,
            'emergency' => $totalEmergencies,
            'acquired' => $totalAcquired,

            'lastPurchaseUpdated' => $lastPurchaseUpdated,
            'lastStoreUpdated' => $lastStoreUpdated,
            'lastRecoveryUpdated' => $lastRecoveryUpdated,
            'lastReturnUpdated' => $lastReturnsUpdated,
            'lastEmergencyUpdated' => $lastEmergenciesUpdated,
            'lastAcquiredUpdated' => $lastAcquiredUpdated,
            
            'users' => $users

        ]);
    }
}
