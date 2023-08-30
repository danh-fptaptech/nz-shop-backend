<?php
use App\Http\Controllers\PostController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SliderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get("/", function () {
    return view("welcome");
});

// Post Routes
Route::get("/post", [PostController::class, "index"])->name("post.index");
Route::get("/post/create", [PostController::class, "create"])->name("post.create");
Route::post("/post/store", [PostController::class, "store"])->name("post.store");
Route::get("/post/edit/{post}", [PostController::class, "edit"])->name("post.edit");
Route::put("/post/edit/{post}", [PostController::class, "update"])->name("post.update");
Route::get("/post/delete/{id}", [PostController::class, "delete"])->name("post.delete");

// Slider Routes
Route::get("/slider", [SliderController::class, "index"])->name("slider.index");
Route::get("/slider/create", [SliderController::class, "create"])->name("slider.create");
Route::post("/slider/store", [SliderController::class, "store"])->name("slider.store");
Route::get("/slider/edit/{slider}", [SliderController::class, "edit"])->name("slider.edit");
Route::put("/slider/edit/{slider}", [SliderController::class, "update"])->name("slider.update");
Route::get("/slider/delete/{id}", [SliderController::class, "delete"])->name("slider.delete");

// Page Routes
Route::get("/page", [PageController::class, "index"])->name("page.index");
Route::get("/page/create", [PageController::class, "create"])->name("page.create");
Route::post("/page/store", [PageController::class, "store"])->name("page.store");
Route::get("/page/edit/{page}", [PageController::class, "edit"])->name("page.edit");
Route::put("/page/edit/{page}", [PageController::class, "update"])->name("page.update");
Route::get("/page/delete/{id}", [PageController::class, "delete"])->name("page.delete");