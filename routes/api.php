<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\ProductVariantController;

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

Route::get("/products", [ProductController::class, "getAllProducts"]);
Route::post("/products", [ProductController::class, "createOneProduct"]);
Route::put("/products/delete/{id}", [ProductController::class, "deleteOneProduct"]);
Route::put("/products/recover/{id}", [ProductController::class, "recoverOneProduct"]);
Route::get("/products/{id}", [ProductController::class, "getProductVariant"]);
Route::put("/products/update/{id}", [ProductController::class, "updateOneProduct"]);

Route::get("/categories", [CategoryController::class, "getAllCategories"]);
Route::post("/categories", [CategoryController::class, "createOneCategory"]);
Route::put("/categories/delete/{id}", [CategoryController::class, "deleteOneCategory"]);
Route::put("/categories/delete-recursively/{id}", [CategoryController::class, "deleteRecursiveCategories"]);
Route::put("/categories/recover/{id}", [CategoryController::class, "recoverOneCategory"]);
Route::put("/categories/recover-recursively/{id}", [CategoryController::class, "recoverRecursiveCategories"]);
Route::get("/categories/{id}", [CategoryController::class, "getSubCategories"]);
Route::put("/categories/update/{id}", [CategoryController::class, "updateOneCategory"]);

Route::put("/variants/delete/{id}", [ProductVariantController::class, "deleteOneVariant"]);
Route::put("/variants/recover/{id}", [ProductVariantController::class, "recoverOneVariant"]);
Route::put("/variants/force-recover/{id}", [ProductVariantController::class, "forceRecoverOneVariant"]);
Route::put("/variants/update/{id}", [ProductVariantController::class, "updateOneVariant"]);
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
