<?php

use App\Http\Controllers\AcquiredController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmergencyRequisitionController;
use App\Http\Controllers\EmergencyRequisitionItemController;
use App\Http\Controllers\EmergencyRequisitionItemSerialController;
use App\Http\Controllers\EmergencyReturnController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\StoreReturnItemController;
use App\Http\Controllers\StoreReturnItemSerialNumberController;
use App\Http\Controllers\RecoveryStoreRequisitionController;
use App\Http\Controllers\RecoveryStoreRequisitionItemController;
use App\Http\Controllers\RecoveryStoreRequisitionItemSerialNumberController;
use App\Http\Controllers\RecoveryStoreController;
use App\Http\Controllers\RecoveryStoreSerialNumberController;
use App\Http\Controllers\StoreItemController;
use App\Http\Controllers\StoreRequisitionController;
use App\Http\Controllers\StoreReturnController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\UserController;
use App\Models\EmergencyReturn;
use Illuminate\Support\Facades\Route;
use League\Csv\Query\Row;

// Profile Route
Route::get('/profile', function () {
    return view('profile'); // Create resources/views/profile.blade.php
})->name('profile')->middleware(['auth', 'prevent.back.history']);



//route to show all acquired purchase requisitions
Route::get('/acquired', [AcquiredController::class, 'index'])
    ->name('acquired.index')
    ->middleware(['auth', 'prevent.back.history']);

//route to show a form to create a new acquired purchase requisition
Route::get('/acquired/create', [AcquiredController::class, 'create'])
    ->name('acquired.create')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/acquired/load-materials', [AcquiredController::class, 'loadMaterials'])->name('acquired.loadMaterials');

//route to store a new acquired purchase requisition
Route::post('/acquired/create', [AcquiredController::class, 'store'])
    ->name('acquired.store')
    ->middleware(['auth', 'prevent.back.history']);

//route to show search results for an acquired purchase requisition
Route::post('/acquired/search', [AcquiredController::class, 'search'])
    ->name('acquired.search')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/acquired/edit/{id}', [AcquiredController::class, 'editForm'])
    ->name('acquired.edit-form')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);
Route::post('/acquired/update/{id}', [AcquiredController::class, 'updateAll'])
    ->name('acquired.update-all')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);

Route::post('/acquired/destroy/{requisition_id}', [AcquiredController::class, 'destroy'])
    ->name('acquired.destroy')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);


//route to show a form to create a new item for an acquired purchase requisition
Route::get('/acquired/item/create/{acquired_id}', [AcquiredController::class, 'create_item'])
    ->name('item.create')
    ->middleware(['auth', 'prevent.back.history']);

//route to store a new item for an acquired purchase requisition
Route::post('/acquired/item/store', [AcquiredController::class, 'store_item'])
    ->name('item.store')
    ->middleware(['auth', 'prevent.back.history']);

// Materials route must come BEFORE the dynamic {id} route
Route::get('/acquired/materials', [AcquiredController::class, 'materialsIndex'])
    ->name('acquired.materials.index')
    ->middleware(['auth', 'prevent.back.history']);

//Route to show all items for an acquired purchase requisition
Route::get('/acquired/items/{id}', [AcquiredController::class, 'index_item'])
    ->name('item.index')
    ->middleware(['auth', 'prevent.back.history']);



// Dashboard Route
Route::get('/', [DashboardController::class, 'create'])
    ->name('dashboard')
    ->middleware(['auth', 'prevent.back.history']);


// StoreRequisition Routes
Route::get('/store/create', [StoreRequisitionController::class, 'create'])
    ->name('create.store')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/store/create', [StoreRequisitionController::class, 'store'])
    ->name('create.store')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/store', [StoreRequisitionController::class, 'index'])
    ->name('store.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/store/search', [StoreRequisitionController::class, 'search'])
    ->name('store.search')
    ->middleware(['auth', 'prevent.back.history']);

// Materials route must come BEFORE the dynamic {requisition_id} route
Route::get('/store/materials', [StoreItemController::class, 'materialsIndex'])
    ->name('store.materials.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/store/destroy/{requisition_id}', [StoreRequisitionController::class, 'destroy'])
    ->name('store.destroy')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);

Route::get('/store/{requisition_id}', [StoreRequisitionController::class, 'show'])
    ->name('store.show')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/store/edit/{requisition_id}', [StoreRequisitionController::class, 'editForm'])
    ->name('store.edit-form')
    ->middleware('auth', 'prevent.back.history', 'isAdmin');

Route::post('/store/update/{requisition_id}', [StoreRequisitionController::class, 'updateAll'])
    ->name('store.update-all')
    ->middleware('auth', 'prevent.back.history', 'isAdmin');






//store items routes
Route::get('/store/items/create/{requisition_id}', [StoreItemController::class, 'create'])
    ->name('store.add-items-form')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/store/items/store', [StoreItemController::class, 'store'])
    ->name('store.add-items')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/store/items/edit/{id}', [StoreItemController::class, 'edit'])
    ->name('store.edit-items-form')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/store/items/edit/', [StoreItemController::class, 'update'])
    ->name('store.edit-items')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/store/items/{requisition_id}/{id}', [StoreItemController::class, 'show'])
    ->name('item.show')
    ->middleware(['auth', 'prevent.back.history']);




// Purchase Requisition Routes
Route::get('purchase/create', [PurchaseRequisitionController::class, 'create'])
    ->name('create.purchase')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/purchase/create', [PurchaseRequisitionController::class, 'store'])
    ->name('store.purchase')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/purchase', [PurchaseRequisitionController::class, 'index'])
    ->name('purchase.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/purchase/search', [PurchaseRequisitionController::class, 'search'])
    ->name('purchase.search')
    ->middleware(['auth', 'prevent.back.history']);
// Materials route must come BEFORE the dynamic {requisition_id} route
Route::get('/purchase/materials', [PurchaseItemController::class, 'materialsIndex'])
    ->name('purchase.materials.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/purchase/{requisition_id}', [PurchaseRequisitionController::class, 'show'])
    ->name('purchase.show')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/purchase/edit/{requisition_id}', [PurchaseRequisitionController::class, 'editForm'])
    ->name('purchase.edit-form')
    ->middleware('auth', 'prevent.back.history', 'isAdmin');

Route::post('/purchase/update/{requisition_id}', [PurchaseRequisitionController::class, 'updateAll'])
    ->name('purchase.update-all')
    ->middleware('auth', 'prevent.back.history', 'isAdmin');

Route::post('/purchase/{requisition_id}', [PurchaseRequisitionController::class, 'destroy'])
    ->name('purchase.destroy')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);




//Recovery store requisition routes
Route::get('recovery/', [RecoveryStoreRequisitionController::class, 'index'])
    ->name('recovery.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('recovery/create', [RecoveryStoreRequisitionController::class, 'create'])
    ->name('recovery.create')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('recovery/store', [RecoveryStoreRequisitionController::class, 'store'])
    ->name('recovery.store')
    ->middleware(['auth', 'prevent.back.history']);

// Materials route must come BEFORE the dynamic {requisition_id} route
Route::get('/recovery/materials', [RecoveryStoreRequisitionItemController::class, 'materialsIndex'])
    ->name('recovery.materials.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('recovery/edit/{requisition_id}', [RecoveryStoreRequisitionController::class, 'editForm'])
    ->name('recovery.edit-form')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('recovery/update/{requisition_id}', [RecoveryStoreRequisitionController::class, 'updateAll'])
    ->name('recovery.update-all')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('recovery/{requisition_id}', [RecoveryStoreRequisitionController::class, 'destroy'])
    ->name('recovery.destroy')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('recovery/search', [RecoveryStoreRequisitionController::class, 'search'])
    ->name('recovery.search')
    ->middleware(['auth', 'prevent.back.history']);
Route::get('/recovery/load-materials/{requisitionId}', [RecoveryStoreRequisitionController::class, 'loadMaterials'])->name('recovery.loadMaterials');

// Recovery items routes
Route::get('recovery-items/{requisition_id}', [RecoveryStoreRequisitionItemController::class, 'index'])
    ->name('recovery-items.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('recovery-items/create/{requisition_id}', [RecoveryStoreRequisitionItemController::class, 'create'])
    ->name('recovery-items.create')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('recovery-items/store', [RecoveryStoreRequisitionItemController::class, 'store'])
    ->name('recovery-items.store')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('recovery/items/search', [RecoveryStoreRequisitionItemController::class, 'search'])
    ->name('recovery-items.search')
    ->middleware(['auth', 'prevent.back.history']);




Route::get('recovered/', [RecoveryStoreController::class, 'index'])
    ->name('recovered.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('recovered/store', [RecoveryStoreController::class, 'store'])
    ->name('recovered.store')
    ->middleware(['auth', 'prevent.back.history']);


Route::post('recovered/search', [RecoveryStoreController::class, 'search'])
    ->name('recovered.search')
    ->middleware(['auth', 'prevent.back.history']);



//return stores serial numbers routes
Route::get('recovered/serial/{recovery_store_id}', [RecoveryStoreSerialNumberController::class, 'index'])
    ->name('serial.index')
    ->middleware(['auth', 'prevent.back.history']);


//Store returns routes

Route::get('returns/', [StoreReturnController::class, 'index'])
    ->name('returns.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('returns/create', [StoreReturnController::class, 'create'])
    ->name('returns.create')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('returns/load-materials/{storeRequisitionId}', [StoreReturnController::class, 'loadMaterials'])
    ->name('returns.loadMaterials')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('returns/store', [StoreReturnController::class, 'store'])
    ->name('returns.store')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('returns/search', [StoreReturnController::class, 'search'])
    ->name('returns.search')
    ->middleware(['auth', 'prevent.back.history']);

// Materials route must come BEFORE the dynamic {requisition_id} route
Route::get('/returns/materials', [StoreReturnItemController::class, 'materialsIndex'])
    ->name('returns.materials.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('returns/edit/{requisition_id}', [StoreReturnController::class, 'editForm'])
    ->name('returns.edit-form')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);

Route::post('returns/update/{requisition_id}', [StoreReturnController::class, 'updateAll'])
    ->name('returns.update-all')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);

Route::post('returns/destroy/{requisition_id}', [StoreReturnController::class, 'destroy'])
    ->name('returns.destroy')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);




// Store return items routes
Route::prefix('returns/items')->name('returns.items.')->middleware(['auth', 'prevent.back.history'])->group(function () {

    Route::post('store', [StoreReturnItemController::class, 'store'])
        ->name('store');

    Route::get('create/{store_return_id}/{requisition_id}', [StoreReturnItemController::class, 'create'])
        ->name('create');

    Route::get('{store_return_id}/{requisition_id}', [StoreReturnItemController::class, 'index'])
        ->name('index');

    Route::post('search', [StoreReturnItemController::class, 'search'])
        ->name('search');
});




//purchase items routes
Route::get('/purchase/items/add/{requisition_id}', [PurchaseItemController::class, 'create'])
    ->name('purchase.add-items-form')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/purchase/items/add/', [PurchaseItemController::class, 'store'])
    ->name('purchase.add-items')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/purchase/items/edit/{id}', [PurchaseItemController::class, 'edit'])
    ->name('purchase.edit-items-form')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/purchase/items/edit/', [PurchaseItemController::class, 'update'])
    ->name('purchase.edit-items')
    ->middleware(['auth', 'prevent.back.history']);



// Recovery items serial numbers routes
Route::get('recovery/items/serial/{item_id}/{requisition_id}', [RecoveryStoreRequisitionItemSerialNumberController::class, 'index'])
    ->name('recoveryItemsSerialIndex')
    ->middleware(['auth', 'prevent.back.history']);



// Stores Route
Route::get('/stores', [StoresController::class, 'index'])
    ->name('stores.index')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/stores/search', [StoresController::class, 'search'])
    ->name('stores.search')
    ->middleware(['auth', 'prevent.back.history']);



// Profile Route
Route::get('/profile', [ProfileController::class, 'index'])
    ->name('profile')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/profile/image/create', [ProfileController::class, 'create'])
    ->name('profileImageCreate')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/profile/image/store', [ProfileController::class, 'store'])
    ->name('profileImageStore')
    ->middleware(['auth', 'prevent.back.history']);



// Login routes
Route::get('/login', [AuthenticationController::class, 'create'])
    ->name('login')->middleware('guest');
Route::post('/login', [AuthenticationController::class, 'store']);

// Logout Route (POST)
Route::post('/logout', [AuthenticationController::class, 'destroy'])
    ->name('logout');




// change password routes
Route::get('/change-password', [PasswordController::class, 'show'])->name('password.change')
    ->middleware('auth');
Route::post('/change-password', [PasswordController::class, 'update'])->name('password.update')
    ->middleware(['auth', 'prevent.back.history']);



//Emergency requisition routes
Route::get('/emergency', [EmergencyRequisitionController::class, 'index'])
    ->name('emergencyIndex')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/emergency/create', [EmergencyRequisitionController::class, 'create'])
    ->name('emergencyCreate')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/emergency/store', [EmergencyRequisitionController::class, 'store'])
    ->name('emergencyStore')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/emergency/destroy/{requisition_id}', [EmergencyRequisitionController::class, 'destroy'])
    ->name('emergencyDestroy')
    ->middleware(['auth', 'prevent.back.history', 'isAdmin']);

Route::post('/emergency/search', [EmergencyRequisitionController::class, 'search'])
    ->name('emergencySearch')
    ->middleware(['auth', 'prevent.back.history']);




//Emergency requisition items routes

Route::get('/emergency/items/{requisition_id}', [EmergencyRequisitionItemController::class, 'index'])
    ->name('emergencyItemsIndex')
    ->middleware(['auth', 'prevent.back.history']);

Route::get('/emergency/items/create/{requisition_id}', [EmergencyRequisitionItemController::class, 'create'])
    ->name('emergencyItemsCreate')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/emergency/items/store/{requisition_id}', [EmergencyRequisitionItemController::class, 'store'])
    ->name('emergencyItemsStore')
    ->middleware(['auth', 'prevent.back.history']);



//Emergency requisition item serials routes
Route::get('/emergency/item/serials/{item_id}', [EmergencyRequisitionItemSerialController::class, 'index'])
    ->name('emergencyItemSerialsIndex')
    ->middleware(['auth', 'prevent.back.history']);



//emergency return routes
Route::get('/emergency/return/create', [EmergencyReturnController::class, 'create'])
    ->name('emergencyReturnCreate')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/emergency/return/store', [EmergencyReturnController::class, 'store'])
    ->name('emergencyReturnStore')
    ->middleware(['auth', 'prevent.back.history']);

Route::post('/emergency/return/confirm/', [EmergencyReturnController::class, 'confirm'])
    ->name('emergencyReturnConfirm')
    ->middleware(['auth', 'prevent.back.history']);



//  Users routes
Route::get('/users', [UserController::class, 'index'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersIndex');

Route::get('/users/create', [UserController::class, 'create'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersCreate');

Route::post('/users/store', [UserController::class, 'store'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersStore');

Route::post('/users/{id}/activate', [UserController::class, 'activate'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersActivate');

Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersDeactivate');

Route::post('/users/{id}/reset', [UserController::class, 'resetPassword'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersReset');

Route::get('/users/{id}', [UserController::class, 'show'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersShow');

Route::post('/users/{id}/makeAdmin', [UserController::class, 'makeAdmin'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersMakeAdmin');

Route::post('/users/{id}/revokeAdmin', [UserController::class, 'revokeAdmin'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersRevokeAdmin');

Route::post('/users/search', [UserController::class, 'search'])
    ->middleware(['auth', 'prevent.back.history', 'isAdmin'])
    ->name('usersSearch');
