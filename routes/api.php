<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ListAddressController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('verify-email', [AuthController::class, 'verify'])->name('verification.verify');
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
//Route::get('isAdmin', [AuthController::class, 'isAdmin']);

Route::middleware('auth:api')->get('isLogin', [AuthController::class, 'isLogin']);
Route::middleware('auth:api')->get('isAdmin', [AuthController::class, 'isAdmin']);
//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::middleware('auth:sanctum')->group(function () {
//  User Controller
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('reSentVerify', [AuthController::class, 'reSentVerify']);
    Route::post('createUser', [AuthController::class, 'createUser']);
    Route::put('updateUser/{id}', [AuthController::class, 'updateUser']);
    Route::put('changeStatusUser/{id}', [AuthController::class, 'changeStatusUser']);
    Route::get('users/{id}', [AuthController::class, 'infoUserID']);
    Route::post('searchUsers', [UserController::class, 'getListUserByQuery']);

//  Role Controller
    Route::post('createRole', [RoleController::class, 'createRole']);
    Route::post('createRoleWithPermissions', [RoleController::class, 'createRoleWithPermissions']);
    Route::post('deleteRole', [RoleController::class, 'deleteRole']);
    Route::put('updateRole/{id}', [RoleController::class, 'updateRole']);
    Route::post('createPermission', [RoleController::class, 'createPermission']);
    Route::post('setRole', [RoleController::class, 'setRole']);
    Route::post('setPermission', [RoleController::class, 'setPermission']);
    Route::get('listRoles', [RoleController::class, 'listRoles']);
    Route::get('listPermissions', [RoleController::class, 'listPermissions']);
//    Route::post('assignRole', [RoleController::class, 'assignRole']);
//    Route::post('removeRole', [RoleController::class, 'removeRole']);
//    Route::post('setRole', [RoleController::class, 'setRole']);
//    Route::post('unsetRole', [RoleController::class, 'unsetRole']);

//    Statistics

    Route::get('userStats', [UserController::class, 'userStats']);
    Route::get('getListUser', [UserController::class, 'getListUser']);

//    Manager Coupon
    Route::post('createCoupon', [CouponController::class, 'createCoupon']);
    Route::get('generateUniqueCode', [CouponController::class, 'generateUniqueCode']);
    Route::get('getListCoupon', [CouponController::class, 'getListCoupon']);
    Route::put('changeStatusCoupon/{id}', [CouponController::class, 'changeStatusCoupon']);
    Route::post('deleteCoupon', [CouponController::class, 'deleteCoupon']);
//
//    Manager ListAddress
    Route::post('createAddress', [ListAddressController::class, 'createAddress']);
    Route::get('showListAddressOfUser', [ListAddressController::class, 'showListAddressOfUser']);
    Route::get('getOneAddressOfUserByID/{id}', [ListAddressController::class, 'getOneAddressOfUserByID']);
});
