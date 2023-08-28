<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
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
Route::post('login', [AuthController::class, 'login']);

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::middleware('auth:sanctum')->group(function () {
//  User Controller
    Route::get('logout', [AuthController::class, 'logout']);
//  Role Controller
    Route::post('createRole', [RoleController::class, 'createRole']);
    Route::post('deleteRole', [RoleController::class, 'deleteRole']);
    Route::put('role/{id}', [RoleController::class, 'updateRole']);
    Route::post('createPermission', [RoleController::class, 'createPermission']);
    Route::post('setRole', [RoleController::class, 'setRole']);
    Route::post('setPermission', [RoleController::class, 'setPermission']);
//    Route::post('assignRole', [RoleController::class, 'assignRole']);
//    Route::post('removeRole', [RoleController::class, 'removeRole']);
//    Route::post('setRole', [RoleController::class, 'setRole']);
//    Route::post('unsetRole', [RoleController::class, 'unsetRole']);
});
