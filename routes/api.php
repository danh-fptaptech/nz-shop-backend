<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use App\Http\Controllers\API\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductVariantController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Description\DescriptionController;

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

Route::post("/description", [DescriptionController::class, "storeImageUpload"]);

Route::get("/products", [ProductController::class, "getAllProducts"]);
Route::post("/products", [ProductController::class, "createOneProduct"]);
Route::put("/products/delete/{id}", [ProductController::class, "deleteOneProduct"]);
Route::put("/products/recover/{id}", [ProductController::class, "recoverOneProduct"]);
Route::get("/products/{id}/variants", [ProductController::class, "getAllVariantsByProductId"]);
Route::get("/products/{id}/variant", [ProductController::class, "getLowPriceVariantByProductId"]);
Route::get("/products/{slug}", [ProductController::class, "getOneProductBySlug"]);
Route::put("/products/update/{id}", [ProductController::class, "updateOneProduct"]);

Route::get("/categories", [CategoryController::class, "getAllCategories"]);
Route::post("/categories", [CategoryController::class, "createOneCategory"]);
Route::put("/categories/delete/{id}", [CategoryController::class, "deleteOneCategory"]);
Route::put("/categories/delete-recursively/{id}", [CategoryController::class, "deleteRecursiveCategories"]);
Route::put("/categories/recover/{id}", [CategoryController::class, "recoverOneCategory"]);
Route::put("/categories/recover-recursively/{id}", [CategoryController::class, "recoverRecursiveCategories"]);
Route::get("/categories/{id}", [CategoryController::class, "getSubCategories"]);
Route::put("/categories/update/{id}", [CategoryController::class, "updateOneCategory"]);
Route::get("/recursive-categories/{id}/products/{numbers?}", [CategoryController::class, "getProductsByRecursiveCategoryId"]);
Route::get("/recursive-categories/{id}", [CategoryController::class, "getRecursiveCategories"]);

Route::put("/variants/delete/{id}", [ProductVariantController::class, "deleteOneVariant"]);
Route::put("/variants/recover/{id}", [ProductVariantController::class, "recoverOneVariant"]);
Route::put("/variants/force-recover/{id}", [ProductVariantController::class, "forceRecoverOneVariant"]);
Route::put("/variants/update/{id}", [ProductVariantController::class, "updateOneVariant"]);

Route::post("/description", [DescriptionController::class, "storeImageUpload"]);
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

Route::get("/posts", [PostController::class, "index"]);
Route::post("/posts", [PostController::class, "store"]);
Route::get("/posts/{id}", [PostController::class, "getOnePost"]);
Route::get("/posts/{title}", [PostController::class, "getPost"]);
Route::put("/posts/edit/{id}", [PostController::class, "update"]);
Route::delete("/posts/delete/{id}", [PostController::class, "delete"]);
Route::get("/randomPosts", [PostController::class, "randomPost"]);

Route::get("/pages", [PageController::class, "index"]);
Route::post("/pages", [PageController::class, "store"]);
Route::get("/pages/{id}", [PageController::class, "getOnePage"]);
Route::put("/pages/edit/{id}", [PageController::class, "update"]);
Route::delete("/pages/delete/{id}", [PageController::class, "delete"]);

Route::get("/sliders", [SliderController::class, "index"]);
Route::post("/sliders", [SliderController::class, "store"]);
Route::get("/sliders/{id}", [SliderController::class, "getOneSlider"]);
Route::put("/sliders/edit/{id}", [SliderController::class, "update"]);
Route::delete("/sliders/delete/{id}", [SliderController::class, "delete"]);


