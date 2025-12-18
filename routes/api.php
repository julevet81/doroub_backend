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
use App\Http\Controllers\Api\PermissionController;
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

    ############################## المشاريع ##############################
    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::put('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

    ############################## المتطوعين ##############################
    Route::get('volunteers/statistics', [VolunteerController::class, 'statistics']);
    Route::apiResource('volunteers', VolunteerController::class)->middleware('permission:عرض المتطوعين');
    

    ############################## انواع المساعدات ##############################
    Route::apiResource('assistance-categories', AssistanceCategoryController::class);

    ############################## عناصر المساعدات ##############################
    Route::apiResource('assistance-items', AssistanceItemController::class);

    ############################## المتبرعين ##############################
    Route::get('donors-assistance-categories', [DonorController::class, 'categories']);
    Route::apiResource('donors', DonorController::class);

    ############################## مساعدات المشاريع ##############################
    Route::apiResource('project-assistances', ProjectAssistanceController::class);

    ############################## المخزون ##############################
    Route::apiResource('inventory-transactions', InventoryTransactionController::class);
    Route::apiResource('inventory-out', InventoryOutController::class);

    ############################## فئات المستفيدين ##############################
    Route::apiResource('beneficiary-categories', BeneficiaryCategoryController::class);

    ##############################  المستفيدين ##############################
    Route::get('beneficiaries/{district}/municipalities', [BeneficiaryController::class, 'getMunicipalities']);
    Route::get('beneficiaries/statistics', [BeneficiaryController::class, 'statistics']);
    Route::get('beneficiaries/statistics', [BeneficiaryController::class, 'statistics']);
    Route::get('municipalities/by-district/{district}', [BeneficiaryController::class, 'getMunicipalities']);
    Route::apiResource('beneficiaries', BeneficiaryController::class);

    ############################## الاطفال ##############################
    Route::apiResource('children', ChildController::class);

    ############################## المالية ##############################
    Route::get('financial-transactions/statistics', [FinancialTransactionController::class, 'statistics']);
    Route::apiResource('financial-transactions', FinancialTransactionController::class);

    ############################## النفقات ##############################
    Route::apiResource('expenses', ExpenseController::class);

    ############################## الاستفادات ##############################
    Route::apiResource('benefices', BeneficeController::class);

    ############################## حركات المساعدات من المخزن ##############################
    Route::apiResource('transaction-items', TransactionItemController::class);

    ############################## معلومات الزوج/الزوجة ##############################
    Route::apiResource('partner-infos', PartnerInfoController::class);

    ############################## الطلبات ##############################
    Route::apiResource('demonds', DemondController::class);
    Route::apiResource('demonded-items', DemondedItemController::class);

    ############################## الاجهزة ##############################
    Route::get('devices/loaned', [DeviceController::class, 'loaned']);
    Route::get('devices/returned', [DeviceController::class, 'returned']);
    Route::get('devices/destructed', [DeviceController::class, 'destructed']);
    Route::put('devices/{device}/destruct', [DeviceController::class, 'destruct']);

    Route::apiResource('devices', DeviceController::class);

    ############################## التسجيلات ##############################
    Route::apiResource('registrations', RegistrationController::class);

    ############################## البلديات ##############################
    Route::apiResource('municipalities', MunicipalityController::class);

    ############################## الدوائر ##############################
    Route::apiResource('districts', DistrictController::class);

    ############################## المستخدمين ##############################
    Route::apiResource('users', UserController::class);

    ############################## الادوار ##############################
    Route::apiResource('roles', RoleController::class);

    ############################## الصلاحيات #############################
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);

    ############################## الاعارات ##############################
    Route::apiResource('loans', LoanController::class);
});
