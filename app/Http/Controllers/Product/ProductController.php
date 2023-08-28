<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private $productRules = [
                "name" => "bail|required|string|min:3|max:25",
                "image" => "bail|required|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048",
                "gallery" => "required",
                'gallery.*' => 'bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048',
                "description" => "bail|required|string",
            ];

    private $productUpdateRules = [
        "name" => "bail|required|string|min:3|max:25",
        "image" => "bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048",
        'gallery.*' => 'bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048',
        "description" => "bail|required|string",
    ];

    private $productMessages = [
                "required" => "Không được bỏ trống!",
                "name.min" => "Tên quá ngắn!",
                "name.max" => "Tên quá dài!",
                "image.required" => "Chưa chọn hình ảnh!",
                "image.max" => "Hình ảnh không được vượt quá 2MB!",
                "image.mimes" => "Tệp hình ảnh phải là tệp jpeg, png, jpg, gif, webp, svg hoặc bmp!",
                "gallery.required" => "Chưa chọn hình ảnh!",
                "gallery.*.max" => "Mỗi tệp hình ảnh không được vượt quá 2MB!",
                "gallery.*.mimes" => "Tệp hình ảnh phải là tệp jpeg, png, jpg, gif, webp, svg hoặc bmp!",
            ];

    private $variantRules = [
                'sku' => "bail|required|string",
                'quantity' => 'bail|required|integer',
                'origin_price' => 'bail|required|numeric|min:1000',
                'sell_price' => 'bail|required|numeric|min:1000',
                'discount_price' => 'bail|numeric|min:1000',
            ];

    private $variantMessages = [
                "required" => "Không được bỏ trống!",
                "integer" => "Phải là số!",
                "numeric" => "Phải là số!",
                "min" => "Giá phải lớn hơn hoặc bằng 1000 VND",
            ];

    private function serverError($data) {
        return response()->json(
            [
                "data" => $data,
                "message"=> "Something went wrong!!"
            ], 
            500
        ); 
    }

    public function getAllProducts() {
        $products = Product::all();
        if (!$products) {
            return $this->serverError("Get all products");
        }
        
        if ($products->count() > 0) {
            return response()->json(
                [
                    "status" => 200,
                    "data" => $products,
                    "message" => "Get all products successfully!"
                ], 
                200
            );
        }

        return response()->noContent();
    }

    public function createOneProduct(Request $request) {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), $this->productRules, $this->productMessages);
        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            $newKey = explode(".", $key)[0];
            $errors[$newKey] = $value[0];
        }

        if (count($errors) > 0) {
            return response()->json(
                [
                    "message" => "Failed in product creation!",
                    "errors" => $errors,
                ],
                400
            );
        }

        $product = new Product();
        $product->name = $request->name;
        $product->slug = $this->create_slug($request->name);
        $product->description = $request->description;

        if ($request->has('category_id')) {
            $product->category_id = $request->category_id;
        }

        $filename = time() . '_' . $request->file("image")->getClientOriginalName();
        $parentPath = "images/product";       
        $imagePath = $request->file("image")->storeAs($parentPath, $filename, 'public');
        $product->image = $imagePath;   
    

        $product->gallery = "";
        foreach ($request->file("gallery") as $index => $image) {
            if ($index !== 0) {
                $product->gallery .= "|";
            }
            $filename = time() . '_' . $image->getClientOriginalName();
            $parentPath = "images/product/gallery";      
            $imagePath = $image->storeAs($parentPath, $filename, 'public');
            $product->gallery .= $imagePath;   
        }

        $product->save();

        if ($product) {
            $response = $this->createVariantsByProductId($product->id);
            DB::commit();
            if ($response) {
                return $response;
            }
            return response()->json(
                [
                    "status" => 201,
                    "data" => $product,
                    "message"=> "Create a product successfully!"
                ], 
                201
            );
        } 

        return $this->serverError("Create a product");
    }

    public function createVariantsByProductId($productId) {
        
        $request = request();
        $more = json_decode($request->more);
        
        $errors = [];
        $isValid = true;

        if (!$request->boolean('type')) {
            $more[0] = (array) $more[0];
            $validator = Validator::make($more[0], $this->variantRules, $this->variantMessages);

            if ($validator->fails()) {
                $isValid = false;
            }

            $error = [];
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[$key] = $value[0]; 
            }
            array_push($errors, $error);
        }
        else {

            foreach ($more as &$item) {
                $item = (array) $item;
                $validator = Validator::make($item, $this->variantRules, $this->variantMessages);

                error_log(print_r($validator->validated(), true));
                if ($validator->fails()) {
                    $isValid = false;
                }

                $error = [];
                foreach ($validator->errors()->messages() as $key => $value) {                
                    $error[$key] = $value[0]; 
                }
                array_push($errors, $error);
            }
        }

        if (!$isValid) {   
            DB::rollback();
            return response()->json(
                [
                    "message" => "Failed in variants creation!",
                    "errors" => $errors,
                ],
                400
            );
        }

        $variants = Product::find($productId)->product_variants()->createMany($more);
        
        if ($variants->count() === 0) {
            DB::rollback();
            return $this->serverError("Create variants");
        }
    }

    public function deleteOneProduct($id) {
        $product = Product::find($id);
        $variants = Product::find($id)->product_variants;
        
        $product->is_deleted = true;
        foreach ($variants as $variant) {
            $variant->is_deleted = true;
            $variant->save();
        }

        $product->save();

        return response()->json(
            [
                "message" => "Delete successfully!"
            ], 
            200
        );
    }

    public function recoverOneProduct($id) {
        $product = Product::find($id);
        $variants = Product::find($id)->product_variants;
        
        $product->is_deleted = false;
        foreach ($variants as $variant) {
            $variant->is_deleted = false;
            $variant->save();
        }

        $product->save();

        return response()->json(
            [
                "message" => "Recover successfully!"
            ], 
            200
        );
    }

    public function getAllVariantsByProductId($id) {
        $variants = Product::find($id)->product_variants;

        return response()->json(
            [
                "status" => 200,
                "data" => ["variants" => $variants],
                "message" => "Get all variants successfully!"
            ], 
            200
        );
    }

    public function getLowPriceVariantByProductId($id) {
        $variant = Product::find($id)->product_variants()->orderBy('sell_price', 'asc')->first();

        return response()->json(
            [
                "status" => 200,
                "data" =>  $variant,
                "message" => "Get low price variant successfully!"
            ], 
            200
        );
    }
    public function updateOneProduct(Request $request, $id) {
        $validator = Validator::make($request->all(), $this->productUpdateRules, $this->productMessages);
        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            $newKey = explode(".", $key)[0];
            $errors[$newKey] = $value[0];
        }

        if ($request->hasFile("image")) {
            $validator = Validator::make($request->all(), $this->productUpdateRules, $this->productMessages);
            foreach ($validator->errors()->messages() as $key => $value) {
                $newKey = explode(".", $key)[0];
                $errors[$newKey] = $value[0];
            }
        }

        if ($request->hasFile("gallery")) {
            $validator = Validator::make($request->all(), $this->productUpdateRules, $this->productMessages);
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

        $product = Product::find($id);

        $product->name = $request->name;
        $product->description = $request->description;

        if ($request->has('category_id')) {
            $product->category_id = $request->category_id;
        }
        
        if ($request->hasFile('image')) {
            Storage::delete("public/$product->image");

            $filename = time() . '_' . $request->file("image")->getClientOriginalName(); 
            $parentPath = "images/product";         
            $imagePath = $request->file("image")->storeAs($parentPath, $filename, 'public');
            $product->image = $imagePath;   
        }

        if ($request->hasFile('gallery')) {
            foreach(explode("|", $product->gallery) as $file) {
                Storage::delete("public/$file");
            }

            $product->gallery = "";
            foreach ($request->file("gallery") as $index => $image) {
                if ($index !== 0) {
                    $product->gallery .= "|";
                }
                $filename = time() . '_' . $image->getClientOriginalName();
                $parentPath = "images/product/gallery";
                
                $imagePath = $image->storeAs($parentPath, $filename, 'public');
                $product->gallery .= $imagePath;   
            }
        }
    
        $product->save();

        return response()->json(
            [
                "message" => "Update a product successfully!"
            ], 
            200
        );
    }

    public function getOneProductBySlug($slug) {
        error_log($slug);
        $product = Product::where('slug', 'like', $slug)->first();
        return response()->json(["data" => $product], 200);
    }

    private function create_slug($string)
    {
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            "/[^a-zA-Z0-9\-\_]/",
        );
        $replace = array(
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
            'd',
            'A',
            'E',
            'I',
            'O',
            'U',
            'Y',
            'D',
            '-',
        );
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        $string = strtolower($string);
        return $string;
    }
}
