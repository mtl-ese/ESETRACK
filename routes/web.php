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
use App\Http\Controllers\RecoveredItemController;
use App\Http\Controllers\RecoveredItemSerialNumberController;
use App\Http\Controllers\RecoveryStoreRequisitionController;
use App\Http\Controllers\RecoveryStoreRequisitionItemController;
use App\Http\Controllers\RecoveryStoreRequisitionItemSerialNumberController;
use App\Http\Controllers\ReturnsStoreController;
use App\Http\Controllers\ReturnsStoreSerialNumberController;
use App\Http\Controllers\StoreItemController;
use App\Http\Controllers\StoreRequisitionController;
use App\Http\Controllers\StoreReturnController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\UserController;
use App\Models\EmergencyReturn;
use Illuminate\Support\Facades\Route;


// Profile Route
Route::get('/profile', function () {
    return view('profile'); // Create resources/views/profile.blade.php
})->name('profile')->middleware('auth');



//route to show all acquired purchase requisitions
Route::get('/acquired', [AcquiredController::class, 'index'])
    ->name('acquired.index')
    ->middleware('auth');

//route to show a form to create a new acquired purchase requisition
Route::get('/acquired/create', [AcquiredController::class, 'create'])
    ->name('acquired.create')
    ->middleware('auth');

//route to store a new acquired purchase requisition
Route::post('/acquired/create', [AcquiredController::class, 'store'])
    ->name('acquired.store')
    ->middleware('auth');

//route to show search results for an acquired purchase requisition
Route::post('/acquired/search', [AcquiredController::class, 'search'])
    ->name('acquired.search')
    ->middleware('auth');

Route::post('/acquired/destroy/{requisition_id}', [AcquiredController::class, 'destroy'])
    ->name('acquired.destroy')
    ->middleware(['auth', 'isAdmin']);


//route to show a form to create a new item for an acquired purchase requisition
Route::get('/acquired/item/create/{acquired_id}', [AcquiredController::class, 'create_item'])
    ->name('item.create')
    ->middleware('auth');

//route to store a new item for an acquired purchase requisition
Route::post('/acquired/item/store', [AcquiredController::class, 'store_item'])
    ->name('item.store')
    ->middleware('auth');

//Route to show all items for an acquired purchase requisition
Route::get('/acquired/items/{id}', [AcquiredController::class, 'index_item'])
    ->name('item.index')
    ->middleware('auth');



// Dashboard Route
Route::get('/', [DashboardController::class, 'create'])
    ->name('dashboard')
    ->middleware('auth');


// StoreRequisition Routes
Route::get('/store/create', [StoreRequisitionController::class, 'create'])
    ->name('create.store')
    ->middleware('auth');

Route::post('/store/create', [StoreRequisitionController::class, 'store'])
    ->name('create.store')
    ->middleware('auth');

Route::get('/store', [StoreRequisitionController::class, 'index'])
    ->name('store.index')
    ->middleware('auth');

Route::post('/store/search', [StoreRequisitionController::class, 'search'])
    ->name('store.search')
    ->middleware('auth');

Route::post('/store/destroy/{requisition_id}', [StoreRequisitionController::class, 'destroy'])
    ->name('store.destroy')
    ->middleware(['auth', 'isAdmin']);

Route::get('/store/{requisition_id}', [StoreRequisitionController::class, 'show'])
    ->name('store.show')
    ->middleware('auth');






//store items routes
Route::get('/store/items/create/{requisition_id}', [StoreItemController::class, 'create'])
    ->name('store.add-items-form')
    ->middleware('auth');

Route::post('/store/items/store', [StoreItemController::class, 'store'])
    ->name('store.add-items')
    ->middleware('auth');

Route::get('/store/items/edit/{id}', [StoreItemController::class, 'edit'])
    ->name('store.edit-items-form')
    ->middleware('auth');

Route::post('/store/items/edit/', [StoreItemController::class, 'update'])
    ->name('store.edit-items')
    ->middleware('auth');

Route::get('/store/items/{requisition_id}/{id}', [StoreItemController::class, 'show'])
    ->name('item.show')
    ->middleware('auth');




// Purchase Requisition Routes
Route::get('purchase/create', [PurchaseRequisitionController::class, 'create'])
    ->name('create.purchase')
    ->middleware('auth');

Route::post('/purchase/create', [PurchaseRequisitionController::class, 'store'])
    ->name('store.purchase')
    ->middleware('auth');

Route::get('/purchase', [PurchaseRequisitionController::class, 'index'])
    ->name('purchase.index')
    ->middleware('auth');

Route::get('/purchase/search', [PurchaseRequisitionController::class, 'search'])
    ->name('purchase.search')
    ->middleware('auth');

Route::get('/purchase/{requisition_id}', [PurchaseRequisitionController::class, 'show'])
    ->name('purchase.show')
    ->middleware('auth');

Route::get('/purchase/edit/{requisition_id}', [PurchaseRequisitionController::class, 'edit'])
    ->name('purchase.edit')
    ->middleware('auth');

Route::post('/purchase/{requisition_id}', [PurchaseRequisitionController::class, 'update'])
    ->name('purchase.update')
    ->middleware('auth');

Route::post('/purchase/{requisition_id}', [PurchaseRequisitionController::class, 'destroy'])
    ->name('purchase.destroy')
    ->middleware(['auth', 'isAdmin']);




//Recovery store requisition routes
Route::get('recovery/', [RecoveryStoreRequisitionController::class, 'index'])
    ->name('recovery.index')
    ->middleware('auth');

Route::get('recovery/create', [RecoveryStoreRequisitionController::class, 'create'])
    ->name('recovery.create')
    ->middleware('auth');

Route::post('recovery/store', [RecoveryStoreRequisitionController::class, 'store'])
    ->name('recovery.store')
    ->middleware('auth');

Route::post('recovery/search', [RecoveryStoreRequisitionController::class, 'search'])
    ->name('recovery.search')
    ->middleware('auth');


Route::post('recovery/destroy/{requisition_id}', [RecoveryStoreRequisitionController::class, 'destroy'])
    ->name('recovery.destroy')
    ->middleware(['auth', 'isAdmin']);





//Recovery store requisition items serials routes

Route::get('recovery/items/serials/{item_id}/{requisition}', [RecoveryStoreRequisitionItemSerialNumberController::class, 'index'])
    ->name('recoveryItemsSerialIndex')
    ->middleware('auth');



//Recovery store requisition items routes

Route::get('recovery/items/{requsition_id}', [RecoveryStoreRequisitionItemController::class, 'index'])
    ->name('recovery-items.index')
    ->middleware('auth');

Route::get('recovery/items/create/{requsition_id}', [RecoveryStoreRequisitionItemController::class, 'create'])
    ->name('recovery-items.create')
    ->middleware('auth');

Route::post('recovery/items/store', [RecoveryStoreRequisitionItemController::class, 'store'])
    ->name('recovery-items.store')
    ->middleware('auth');

Route::post('recovery/items/search', [RecoveryStoreRequisitionItemController::class, 'search'])
    ->name('recovery-items.search')
    ->middleware('auth');





//Store returns routes

Route::get('returns/', [StoreReturnController::class, 'index'])
    ->name('returns.index')
    ->middleware('auth');

Route::get('returns/create', [StoreReturnController::class, 'create'])
    ->name('returns.create')
    ->middleware('auth');

Route::post('returns/store', [StoreReturnController::class, 'store'])
    ->name('returns.store')
    ->middleware('auth');

Route::post('returns/search', [StoreReturnController::class, 'search'])
    ->name('returns.search')
    ->middleware('auth');

Route::post('returns/destroy/{requisition_id}', [StoreReturnController::class, 'destroy'])
    ->name('returns.destroy')
    ->middleware(['auth', 'isAdmin']);







//return stores routes

Route::post('return/store', [ReturnsStoreController::class, 'store'])
    ->name('return.store')
    ->middleware('auth');

Route::get('return/', [ReturnsStoreController::class, 'index'])
    ->name('return.index')
    ->middleware('auth');

Route::post('return/search', [ReturnsStoreController::class, 'search'])
    ->name('return.search')
    ->middleware('auth');





//return stores serial numbers routes
Route::get('return/serial/{returns_store_id}', [ReturnsStoreSerialNumberController::class, 'index'])
    ->name('serial.index')
    ->middleware('auth');



//Recovered items serial number routes
Route::get('recovered/items/serial/{store_return_id}/{requisition_id}', [RecoveredItemSerialNumberController::class, 'index'])
    ->name('recoveredItemSerialIndex')
    ->middleware('auth');


//recovered items routes
Route::post('recovered/items/store', [RecoveredItemController::class, 'store'])
    ->name('recovered-items.store')
    ->middleware('auth');

Route::post('recovered/items/search', [RecoveredItemController::class, 'store'])
    ->name('recovered-items.store')
    ->middleware('auth');

Route::get('recovered/items/create/{store_return_id}/{requisition_id}', [RecoveredItemController::class, 'create'])
    ->name('recovered-items.create')
    ->middleware('auth');

Route::get('recovered/items/{store_return_id}/{requisition_id}', [RecoveredItemController::class, 'index'])
    ->name('recovered-items.index')
    ->middleware('auth');




//purchase items routes
Route::get('/purchase/items/add/{requisition_id}', [PurchaseItemController::class, 'create'])
    ->name('purchase.add-items-form')
    ->middleware('auth');

Route::post('/purchase/items/add/', [PurchaseItemController::class, 'store'])
    ->name('purchase.add-items')
    ->middleware('auth');

Route::get('/purchase/items/edit/{id}', [PurchaseItemController::class, 'edit'])
    ->name('purchase.edit-items-form')
    ->middleware('auth');

Route::post('/purchase/items/edit/', [PurchaseItemController::class, 'update'])
    ->name('purchase.edit-items')
    ->middleware('auth');





// Stores Route
Route::get('/stores', [StoresController::class, 'index'])
    ->name('stores.index')
    ->middleware('auth');

Route::get('/stores/search', [StoresController::class, 'search'])
    ->name('stores.search');



// Profile Route
Route::get('/profile', [ProfileController::class, 'index'])
    ->name('profile')
    ->middleware('auth');

Route::get('/profile/image/create', [ProfileController::class, 'create'])
    ->name('profileImageCreate')
    ->middleware('auth');

Route::post('/profile/image/store', [ProfileController::class, 'store'])
    ->name('profileImageStore')
    ->middleware('auth');



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
    ->middleware('auth');



//Emergency requisition routes
Route::get('/emergency', [EmergencyRequisitionController::class, 'index'])
    ->name('emergencyIndex')
    ->middleware('auth');

Route::get('/emergency/create', [EmergencyRequisitionController::class, 'create'])
    ->name('emergencyCreate')
    ->middleware('auth');

Route::post('/emergency/store', [EmergencyRequisitionController::class, 'store'])
    ->name('emergencyStore')
    ->middleware('auth');

Route::post('/emergency/destroy/{requisition_id}', [EmergencyRequisitionController::class, 'destroy'])
    ->name('emergencyDestroy')
    ->middleware(['auth', 'isAdmin']);

Route::post('/emergency/search', [EmergencyRequisitionController::class, 'search'])
    ->name('emergencySearch')
    ->middleware('auth');




//Emergency requisition items routes

Route::get('/emergency/items/{requisition_id}', [EmergencyRequisitionItemController::class, 'index'])
    ->name('emergencyItemsIndex')
    ->middleware('auth');

Route::get('/emergency/items/create/{requisition_id}', [EmergencyRequisitionItemController::class, 'create'])
    ->name('emergencyItemsCreate')
    ->middleware('auth');

Route::post('/emergency/items/store/{requisition_id}', [EmergencyRequisitionItemController::class, 'store'])
    ->name('emergencyItemsStore')
    ->middleware('auth');



//Emergency requisition item serials routes
Route::get('/emergency/item/serials/{item_id}', [EmergencyRequisitionItemSerialController::class, 'index'])
    ->name('emergencyItemSerialsIndex')
    ->middleware('auth');



//emergency return routes
Route::get('/emergency/return/create', [EmergencyReturnController::class, 'create'])
    ->name('emergencyReturnCreate')
    ->middleware('auth');

Route::post('/emergency/return/store', [EmergencyReturnController::class, 'store'])
    ->name('emergencyReturnStore')
    ->middleware('auth');



//  Users routes
Route::get('/users', [UserController::class, 'index'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersIndex');

Route::get('/users/create', [UserController::class, 'create'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersCreate');

Route::post('/users/store', [UserController::class, 'store'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersStore');

Route::post('/users/{id}/activate', [UserController::class, 'activate'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersActivate');

Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersDeactivate');

Route::post('/users/{id}/reset', [UserController::class, 'resetPassword'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersReset');

Route::get('/users/{id}', [UserController::class, 'show'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersShow');

Route::post('/users/{id}/makeAdmin', [UserController::class, 'makeAdmin'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersMakeAdmin');

Route::post('/users/{id}/revokeAdmin', [UserController::class, 'revokeAdmin'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersRevokeAdmin');

Route::post('/users/search', [UserController::class, 'search'])
    ->middleware(['auth', 'isAdmin'])
    ->name('usersSearch');