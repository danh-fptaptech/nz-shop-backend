<?php

use App\Http\Controllers\API_PROXY_GHTK;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\GenerateImageController;
use App\Http\Controllers\ListAddressController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Comment\PostCommentController;
use App\Http\Controllers\Comment\ProductCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\Description\DescriptionController;
use App\Http\Controllers\Comment\PostFeedbackController;
use App\Http\Controllers\Comment\ProductFeedbackController;
use App\Http\Controllers\GoogleController;

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


//_____________________________________________________________________
//  Public
Route::post('register', [AuthController::class, 'register']);
Route::post('verify-email', [AuthController::class, 'verify'])->name('verification.verify');
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
//Route::get('isAdmin', [AuthController::class, 'isAdmin']);

Route::get('fetchPublicDSetting', [SiteSettingController::class, 'fetchPublicDSetting']);
Route::get('isLogin', [AuthController::class, 'isLogin']);
Route::middleware('auth:api')->get('isAdmin', [AuthController::class, 'isAdmin']);
Route::post('getValueByCode', [CouponController::class, 'getValueByCode']);
Route::get('verSetting', [SiteSettingController::class, 'verSetting']);
Route::get('fetchPublicSetting', [SiteSettingController::class, 'fetchPublicSetting']);
//Route::get('/generateImage/{width}/{height}/{background}/{text}', [GenerateImageController::class, 'generate']);
Route::post('API_PROXY_GHTK', [API_PROXY_GHTK::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    //_____________________________________________________________________
    //Function - Global - Auth
    Route::get('logout', [AuthController::class, 'logout']);

    //_____________________________________________________________________
    //  User Controller
    Route::get('reSentVerify', [AuthController::class, 'reSentVerify']);
    Route::post('createUser', [AuthController::class, 'createUser']);
    Route::put('updateUser/{id}', [AuthController::class, 'updateUser']);
    Route::put('changeStatusUser/{id}', [AuthController::class, 'changeStatusUser']);
    Route::get('users/{id}', [AuthController::class, 'infoUserID']);
    Route::post('searchUsers', [UserController::class, 'getListUserByQuery']);
    Route::get('getListUser', [UserController::class, 'getListUser']);

    //_____________________________________________________________________
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

    //_____________________________________________________________________
    //    Statistics
    Route::get('userStats', [UserController::class, 'userStats']);


    //_____________________________________________________________________
    //    Manager Coupon
    Route::post('createCoupon', [CouponController::class, 'createCoupon']);
    Route::get('generateUniqueCode', [CouponController::class, 'generateUniqueCode']);
    Route::get('getListCoupon', [CouponController::class, 'getListCoupon']);
    Route::put('changeStatusCoupon/{id}', [CouponController::class, 'changeStatusCoupon']);
    Route::post('deleteCoupon', [CouponController::class, 'deleteCoupon']);
    Route::put('updateCoupon/{id}', [CouponController::class, 'updateCoupon']);

    //_____________________________________________________________________
    //    Manager ListAddress
    Route::post('createAddress', [ListAddressController::class, 'createAddress']);
    Route::get('showListAddressOfUser', [ListAddressController::class, 'showListAddressOfUser']);
    Route::get('getOneAddressOfUserByID/{id}', [ListAddressController::class, 'getOneAddressOfUserByID']);
    Route::put('editAddressByID/{id}', [ListAddressController::class, 'editAddressByID']);


    //_____________________________________________________________________
    //    Site Setting
    Route::post('createOne', [SiteSettingController::class, 'createOne']);
    Route::put('updateSetting', [SiteSettingController::class, 'updateSetting']);
    Route::post('getSecretKey', [SiteSettingController::class, 'getSecretKey']);
    Route::get('newSecretKey', [SiteSettingController::class, 'newSecretKey']);
    Route::get('newIdApp', [SiteSettingController::class, 'newIdApp']);
    Route::get('fetchGSetting', [SiteSettingController::class, 'fetchGSetting']);
    Route::get('fetchMSetting', [SiteSettingController::class, 'fetchMSetting']);
    Route::get('fetchSSetting', [SiteSettingController::class, 'fetchSSetting']);
    Route::get('fetchSEOSetting', [SiteSettingController::class, 'fetchSEOSetting']);
    Route::get('fetchDSetting', [SiteSettingController::class, 'fetchDSetting']);
});
// Long
Route::post("/description", [DescriptionController::class, "storeImageUpload"]);
Route::get("/products/name/{name}", [ProductController::class, "getProductsByName"]);
Route::get("/products", [ProductController::class, "getAllProducts"]);
Route::get("/randomProducts", [ProductController::class, "randomProducts"]);
Route::post("/products", [ProductController::class, "createOneProduct"]);
Route::put("/products/toggle/{id}", [ProductController::class, "toggleOneProduct"]);
Route::get("/products/{slug}", [ProductController::class, "getOneProductBySlug"]);
Route::get("/products/id/{id}", [ProductController::class, "getOneProductById"]);
Route::put("/products/update/{id}", [ProductController::class, "updateOneProduct"]);
Route::get("/products/{id}/comments", [ProductController::class, "getAllComments"]);
Route::get("/sku", [ProductController::class, "generateSku"]);
Route::get("/product-pagination", [ProductController::class, "getProductPagination"]);
Route::delete("/products/delete/{id}", [ProductController::class, "deleteOneProduct"]);
Route::post("/products/sku", [ProductController::class, "getOneProductBySku"]);
Route::get("/out-stock", [ProductController::class, "outStock"]);
Route::get("/search-output/{input}", [ProductController::class, "getSearchOutput"]);
Route::get("/average-review/{id}", [ProductController::class, "getAverageReview"]);
Route::get("/products/{id}/reviews", [ProductController::class, "getAllReviews"]);

Route::get("/categories", [CategoryController::class, "getAllCategories"]);
Route::post("/categories", [CategoryController::class, "createOneCategory"]);
Route::put("/categories/delete/{id}", [CategoryController::class, "deleteOneCategory"]);
Route::put("/categories/disable/{id}", [CategoryController::class, "disableRecursiveCategories"]);
Route::put("/categories/enable/{id}", [CategoryController::class, "enableRecursiveCategories"]);
Route::get("/categories/{id}", [CategoryController::class, "getSubCategories"]);
Route::put("/categories/update/{id}", [CategoryController::class, "updateOneCategory"]);
Route::get("/recursive-categories/{id}/products/{numbers?}", [CategoryController::class, "getFinalProductsByRecursiveCategoryId"]);
Route::get("/recursive-categories/{id}", [CategoryController::class, "getRecursiveCategories"]);
Route::get("/category-pagination", [CategoryController::class, "getCategoryPagination"]);

//Tam
Route::get("/users", [UserController::class, "getAllUsers"]);

// Route::get("/posts", [PostController::class, "getAllPosts"]);
Route::get("/posts/{id}/comments", [PostController::class, "getAllComments"]);

Route::get("/reviews", [ReviewController::class, "getAllReviews"]);
Route::post("/reviews", [ReviewController::class, "createOneReview"]);
Route::put("/reviews/toggleApprove/{id}", [ReviewController::class, "toggleApproveOneReview"]);
Route::put("/reviews/delete/{id}", [ReviewController::class, "deleteOneReview"]);
Route::get("/review-pagination", [ReviewController::class, "getReviewPagination"]);

Route::get("/post-comments", [PostCommentController::class, "getAllCommentsPost"]);
Route::post("/post-comments", [PostCommentController::class, "createOneComment"]);
Route::put("/post-comments/toggle/{id}", [PostCommentController::class, "toggleApproveOneCommentPost"]);
Route::delete("/post-comments/delete/{id}", [PostCommentController::class, "deleteOneCommentPost"]);
Route::get("/post-comments/{id}/post-feedbacks", [PostCommentController::class, "getAllPostFeedBacksById"]);
Route::get("/post-comment-pagination", [PostCommentController::class, "getCommentPagination"]);
Route::get("/post-comment-pagination/{id}/feedback", [PostCommentController::class, "getFeedbackCommentPagination"]);

Route::get("/product-comments", [ProductCommentController::class, "getAllCommentsProduct"]);
Route::post("/product-comments", [ProductCommentController::class, "createOneCommentProduct"]);
Route::put("/product-comments/toggle/{id}", [ProductCommentController::class, "toggleApproveOneCommentProduct"]);
Route::delete("/product-comments/delete/{id}", [ProductCommentController::class, "deleteOneCommentProduct"]);
Route::get("/product-comments/{id}/product-feedbacks", [ProductCommentController::class, "getAllProductFeedBacksById"]);
Route::get("/product-comment-pagination", [ProductCommentController::class, "getCommentPagination"]);
Route::get("/product-comment-pagination/{id}/feedback", [ProductCommentController::class, "getFeedbackCommentPagination"]);

//feedback
Route::put("/product-feedbacks/toggle/{id}", [ProductFeedbackController::class, "toggleApproveOneCommentProduct"]);
Route::put("/post-feedbacks/toggle/{id}", [PostFeedbackController::class, "toggleApproveOneCommentProduct"]);
Route::delete("/product-feedbacks/delete/{id}", [ProductFeedbackController::class, "deleteOneCommentProduct"]);
Route::delete("/post-feedbacks/delete/{id}", [PostFeedbackController::class, "deleteOneCommentProduct"]);

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

// Google Sign In
Route::post('/get-google-sign-in-url/{provider}', [GoogleController::class, 'getGoogleSignInUrl']);
Route::get('/login/{provider}/callback', [GoogleController::class, 'loginCallback']);