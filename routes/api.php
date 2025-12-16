<?php

use App\Http\Controllers\Api\AssistanceCategoryController;
use App\Http\Controllers\Api\AssistanceItemController;
use App\Http\Controllers\Api\BeneficeController;
use App\Http\Controllers\Api\BeneficiaryCategoryController;
use App\Http\Controllers\Api\BeneficiaryController;
use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\DemondController;
use App\Http\Controllers\Api\DemondedItemController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\DonorController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FinancialTransactionController;
use App\Http\Controllers\Api\InventoryOutController;
use App\Http\Controllers\Api\InventoryTransactionController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\MunicipalityController;
use App\Http\Controllers\Api\PartnerInfoController;
use App\Http\Controllers\Api\ProjectAssistanceController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TransactionItemController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VolunteerController;
use App\Http\Controllers\Api\Auth\{
    RegisterController,
    LoginController,
    LogoutController,
    ProfileController,
    PasswordController,
    EmailVerificationController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::post('/forgot-password', [PasswordController::class, 'forgot']);
Route::post('/reset-password', [PasswordController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::post('/logout', [LogoutController::class, 'logout']);

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend']);
});

Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

// Auth (Breeze / Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('projects', ProjectController::class);

    Route::apiResource('volunteers', VolunteerController::class);
    Route::get('volunteers/statistics', [VolunteerController::class, 'statistics']);

    Route::apiResource('assistance-categories', AssistanceCategoryController::class);

    Route::apiResource('assistance-items', AssistanceItemController::class);

    Route::get('donors-assistance-categories', [DonorController::class, 'categories']);
    Route::apiResource('donors', DonorController::class);

    Route::apiResource('project-assistances', ProjectAssistanceController::class);

    Route::apiResource('inventory-transactions', InventoryTransactionController::class);
    Route::apiResource('inventory-out', InventoryOutController::class);

    Route::apiResource('beneficiary-categories', BeneficiaryCategoryController::class);

    Route::get('beneficiaries/{district}/municipalities', [BeneficiaryController::class, 'getMunicipalities']);
    Route::get('beneficiaries/statistics', [BeneficiaryController::class, 'statistics']);

    Route::apiResource('beneficiaries', BeneficiaryController::class);
    Route::get('beneficiaries/statistics', [BeneficiaryController::class, 'statistics']);
    Route::get('municipalities/by-district/{district}', [BeneficiaryController::class, 'getMunicipalities']);

    Route::apiResource('children', ChildController::class);

    Route::get('financial-transactions/statistics', [FinancialTransactionController::class, 'statistics']);
    Route::apiResource('financial-transactions', FinancialTransactionController::class);


    Route::apiResource('expenses', ExpenseController::class);

    Route::apiResource('benefices', BeneficeController::class);

    Route::apiResource('transaction-items', TransactionItemController::class);

    Route::apiResource('partner-infos', PartnerInfoController::class);

    Route::apiResource('demonds', DemondController::class);
    Route::apiResource('demonded-items', DemondedItemController::class);

    // Devices
    Route::get('devices/loaned', [DeviceController::class, 'loaned']);
    Route::get('devices/returned', [DeviceController::class, 'returned']);
    Route::get('devices/destructed', [DeviceController::class, 'destructed']);
    Route::put('devices/{device}/destruct', [DeviceController::class, 'destruct']);

    Route::apiResource('devices', DeviceController::class);

    Route::apiResource('registrations', RegistrationController::class);

    Route::apiResource('municipalities', MunicipalityController::class);
    Route::apiResource('districts', DistrictController::class);

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('loans', LoanController::class);
});
