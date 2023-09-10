<?php

namespace App\Http\Controllers\Product;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    private $rules = [
                "name" => "bail|required|string|min:2|max:25",
                "image" => "bail|required|mimes:jpeg,jpg,png,gif,bmp,svg,webp|max:2048",
                "description" => "bail|required|string",
            ];
    
    private $updateRules = [
                "name" => "bail|required|string|min:2|max:25",
                "image" => "bail|mimes:jpeg,jpg,png,gif,bmp,svg,webp|max:2048",
                "description" => "bail|required|string",
            ];

    private $messages = [
                "required" => "Không được bỏ trống!",
                "name.min" => "Tên quá ngắn!",
                "name.max" => "Tên quá dài!",
                "image.required" => "Chưa chọn hình ảnh!",
                "image.max" => "Hình ảnh không được vượt quá 2MB!",
                "image.mimes" => "Tệp hình ảnh phải là tệp jpeg, png, jpg, gif, svg, webp hoặc bmp!",
            ];

    public function getAllCategories() {
        $categories = Category::where('is_disabled', false)->get();
        if ($categories->count() > 0) {
            return response()->json(
                [
                    "data" => $categories,
                    "message" => "Get all categories successfully!"
                ], 
                200
            );
        }

        return response()->noContent();
    }

    public function createOneCategory(Request $request) {
        $validator = Validator::make($request->all(), $this->rules, $this->messages);
        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            $newKey = explode(".", $key)[0];
            $errors[$newKey] = $value[0];
        }

        if (!$request->has('parent_category_id')) {
            $validator = Validator::make(
                $request->all(), 
                ["icon" => "bail|required|mimes:svg|max:2048"], 
                [
                    "icon.required" => "Chưa chọn icon!",
                    "icon.max" => "Icon không được vượt quá 2MB!",
                    "icon.mimes" => "Icon phải là tệp svg!",
                ]
            );
            foreach ($validator->errors()->messages() as $key => $value) {
                $newKey = explode(".", $key)[0];
                $errors[$newKey] = $value[0];
            }
        }

        if (count($errors) !== 0) {
            return response()->json(
                [
                    "errors" => $errors,
                ],
                400
            );
        }

        $category = new Category();
        $category->name = $request->name;

        $filename = time() . '_' . $request->file("image")->getClientOriginalName();
        $parentPath = "images/category"; 
        $imagePath = $request->file("image")->storeAs($parentPath, $filename, 'public');
        $category->image = $imagePath;   

        $category->description = $request->description; 

        if ($request->has('is_brand')) {
            $category->is_brand = $request->boolean('is_brand'); 
        }

        if ($request->has('parent_category_id')) {
            $category->parent_category_id = $request->parent_category_id; 
        }
 
        if ($request->hasFile("icon")) {
            $filename = time() . '_' . $request->file("icon")->getClientOriginalName();
            $parentPath = "images/category/icon";  
            $imagePath = $request->file("icon")->storeAs($parentPath, $filename, 'public');
            $category->icon = $imagePath;
        }      

        $category->save();

        return response()->json(
            [
                "message" => "Create a category successfully!"
            ], 
            201
        );
    }
    
    public function deleteOneCategory($id) {
        $category = Category::find($id);
        
        $category->delete();

        return response()->json(
            [
                "message" => "Delete successfully!"
            ], 
            200
        );
    }

    public function disableRecursiveCategories($id) {
        $category = Category::find($id);

        $childCategories = Category::where("parent_category_id", $category->id)->get();
        
        if ($childCategories->count() > 0) {    
            foreach ($childCategories as $childCategory) {
                $this->disableRecursiveCategories($childCategory->id);
            }
        }
                 
        $category->is_disabled = true;
        $category->save();

        return response()->json(
            [
                "message" => "Disable successfully!"
            ], 
            200
        );
    }

    public function enableRecursiveCategories($id) {
        $category = Category::find($id);
        
        if ($category->parent_category_id) {    
            $this->enableRecursiveCategories($category->parent_category_id);   
        }
                 
        $category->is_disabled = false;
        $category->save();

        return response()->json(
            [
                "message" => "Enable successfully!"
            ], 
            200
        );
    }

    public function getSubCategories($id) {
        $childCategories = Category::where("parent_category_id", $id)->where("is_disabled", false)->get();

        if ($childCategories->count() > 0) {
            return response()->json(
                [
                    "data" => $childCategories,
                    "message" => "Get all categories successfully!"
                ], 
                200
            );
        }

        return response()->noContent();
    }

    public function updateOneCategory(Request $request, $id) {
        $validator = Validator::make($request->all(), $this->updateRules, $this->messages);
        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            $newKey = explode(".", $key)[0];
            $errors[$newKey] = $value[0];
        }

        if (!$request->has('parent_category_id') && $request->hasFile("icon")) {
            $validator = Validator::make(
                $request->all(), 
                ["icon" => "bail|mimes:svg|max:2048"], 
                [
                    "icon.max" => "Icon không được vượt quá 2MB!",
                    "icon.mimes" => "Icon phải là tệp svg!",
                ]
            );
            foreach ($validator->errors()->messages() as $key => $value) {
                $newKey = explode(".", $key)[0];
                $errors[$newKey] = $value[0];
            }
        }

        if (count($errors) !== 0) {
            return response()->json(
                [
                    "errors" => $errors,
                ],
                400
            );
        }

        $category = Category::find($id);

        $category->name = $request->name;
        
        if ($request->hasFile('image')) {
            Storage::delete("public/$category->image");

            $filename = time() . '_' . $request->file("image")->getClientOriginalName(); 
            $parentPath = "images/category";
            $imagePath = $request->file("image")->storeAs($parentPath, $filename, 'public');
            $category->image = $imagePath;   
        }

        $category->description = $request->description;

        if ($request->has('is_brand')) {
            $category->is_brand = $request->boolean('is_brand'); 
        }

        if ($request->has('parent_category_id')) {
            $category->parent_category_id = $request->parent_category_id; 
        }

        if (!$request->has('parent_category_id') && $request->hasFile("icon")) {
            Storage::delete("public/$category->icon");

            $filename = time() . '_' . $request->file("icon")->getClientOriginalName();
            $parentPath = "images/category/icon";
            $imagePath = $request->file("icon")->storeAs($parentPath, $filename, 'public');
            $category->icon = $imagePath;
        }

        $category->save();

        return response()->json(
            [
                "message" => "Update a category successfully!"
            ], 
            200
        );
    }

    public function getFinalProductsByRecursiveCategoryId($id, $numbers = null) {
        $products = $this->getProductsByRecursiveCategoryId($id, $numbers);
        foreach ($products as $product) {
            $product->rating = $product->reviews()->avg('rating');
            $product->ratingCount = $product->reviews()->count('rating');
        }
        return response()->json([
            "data" =>  $products,
            "message" => "Get successfully!"
        ], 200);
    }

    public function getProductsByRecursiveCategoryId($id, $numbers = null) {
        $firstCategoryArray = Category::find($id);
        $categories = $this->getRecursiveCategories($id)->push($firstCategoryArray);
        $products = new Collection;
        foreach ($categories as $category) {
            $products = $products->merge($category->products);
        }
        if ($numbers && $products->count() > (int) $numbers) {
            $products = $products->random($numbers);
        }

        return $products;
    }

    public function getRecursiveCategories($id, &$result = new Collection) {
        $categories = Category::where("parent_category_id", $id)->get();
        $result = $result->merge($categories);
        foreach ($categories as $category) {
            $this->getRecursiveCategories($category->id, $result);
        }
        return $result;
    }

     public function getCategoryPagination() {
        $categories = DB::table('categories');

        if (request()->query('is_disabled')) {
            $categories = $categories->where('is_disabled', '=', request()->boolean('is_disabled'));
        }

        if (request()->query("name")) {
            $categories = $categories->where("name", "like", "%" . request()->query('name') . "%");
        }

        if (request()->query("per_page")) {
            $categories = $categories->paginate(request()->query("per_page"));
        }

        return response()->json(["data" => [
            "categories" => $categories->items(),
            "numberOfPages" => $categories->lastPage(),
        ]], 200);
    }

}
