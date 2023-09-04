<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\Comment\PostCommentController;
use App\Http\Controllers\Comment\ProductCommentController;
use App\Http\Controllers\Comment\PostFeedbackController;
use App\Http\Controllers\Comment\ProductFeedbackController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\Product\ProductController;
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

Route::get("/users", [UserController::class, "getAllUsers"]);

Route::get("/products", [ProductController::class, "getAllProducts"]);
Route::get("/products/{id}/comments", [ProductController::class, "getAllComments"]);
Route::get("/products/{id}/reviews", [ProductController::class, "getAllReviews"]);

Route::get("/posts", [PostController::class, "getAllPosts"]);
Route::get("/posts/{id}/comments", [PostController::class, "getAllComments"]);

Route::get("/reviews", [ReviewController::class, "getAllReviews"]);
Route::post("/reviews", [ReviewController::class, "createOneReview"]);
Route::put("/reviews/approve/{id}", [ReviewController::class, "approveOneReview"]);
Route::put("/reviews/delete/{id}", [ReviewController::class, "deleteOneReview"]);

Route::get("/post-comments", [PostCommentController::class, "getAllComments"]);
Route::post("/post-comments", [PostCommentController::class, "createOneComment"]);
Route::put("/post-comments/approve/{id}", [PostCommentController::class, "approveOneCommentPost"]);
Route::put("/post-comments/delete/{id}", [PostCommentController::class, "deleteOneCommentPost"]);
Route::put("/post-comments/delete-all/{id}", [PostCommentController::class, "deleteAllCommentsProduct"]);
Route::get("/post-comments/{id}/post-feedbacks", [PostCommentController::class, "getAllPostFeedBacksById"]);
Route::get("/post-comments/{id}/user", [PostCommentController::class, "getUserByCommentId"]);
Route::get("/post-comments/{id}/post", [PostCommentController::class, "getPostByCommentId"]);

Route::get("/product-comments", [ProductCommentController::class, "getAllCommentsProduct"]);
Route::post("/product-comments", [ProductCommentController::class, "createOneCommentProduct"]);
Route::put("/product-comments/approve/{id}", [ProductCommentController::class, "approveOneCommentProduct"]);
Route::put("/product-comments/delete/{id}", [ProductCommentController::class, "deleteOneCommentProduct"]);
Route::put("/product-comments/delete-all/{id}", [ProductCommentController::class, "deleteAllCommentsProduct"]);
Route::get("/product-comments/{id}/product-feedbacks", [ProductCommentController::class, "getAllProductFeedBacksById"]);
Route::get("/product-comments/{id}/user", [ProductCommentController::class, "getUserByCommentId"]);
Route::get("/product-comments/{id}/product", [ProductCommentController::class, "getProductByCommentId"]);

Route::post("/product-feedbacks", [ProductFeedbackController::class, "createOneFeedBack"]);
Route::put("/product-feedbacks/delete/{id}", [ProductFeedbackController::class, "deleteOneFeedBack"]);
Route::put("/product-feedbacks/approve/{id}", [ProductFeedbackController::class, "approveOneFeedBack"]);
Route::put("/product-feedbacks/approve-all/{id}", [ProductFeedbackController::class, "approveAllComments"]);

Route::post("/post-feedbacks", [PostFeedbackController::class, "createOneFeedBack"]);
Route::put("/post-feedbacks/delete/{id}", [PostFeedbackController::class, "deleteOneFeedBack"]);
Route::put("/post-feedbacks/approve/{id}", [PostFeedbackController::class, "approveOneFeedBack"]);
Route::put("/post-feedbacks/approve-all/{id}", [PostFeedbackController::class, "approveAllComments"]);

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
