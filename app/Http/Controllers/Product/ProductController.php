<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use Image;

class ProductController extends Controller
{
    private $productRules = [
        "sku" => "bail|required|regex:/^([A-Z0-9]+)$/|unique:products|min:2|max:10",
        "name" => "bail|required|regex:/([\p{L}0-9]+)$/u|min:3|max:100",
        "image" => "bail|required|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048",
        "gallery" => "required",
        'gallery.*' => 'bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048',
        "description" => "bail|required|string",
        'quantity' => 'bail|required|integer',
        'origin_price' => 'bail|required|numeric',
        'sell_price' => 'bail|required|numeric|gte:origin_price',
        'discount_price' => 'bail|numeric|lt:sell_price|nullable',
        "start_date" => 'bail|required_with:discount_price|date',
        "end_date" => "bail|required_with:discount_price|date|after:start_date",
    ];

    private $variantRules = [
        'quantity' => 'bail|required|integer',
        'originPrice' => 'bail|required|numeric',
        'sellPrice' => 'bail|required|numeric|gte:originPrice',
        'discountPrice' => 'bail|numeric|lt:sellPrice|nullable',
        "startDate" => 'bail|required_with:discountPrice|date',
        "endDate" => "bail|required_with:discountPrice|date|after:startDate",
    ];

    private $productUpdateRules = [
        "name" => "bail|required|string|min:3|max:100",
        "image" => "bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048",
        'gallery.*' => 'bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048',
        "description" => "bail|required|string",
    ];

    private $productMessages = [
        "integer" => "Phải là số!",
        "numeric" => "Phải là số!",
        "required" => "Không được bỏ trống!",
        "name.min" => "Tên quá ngắn!",
        "name.max" => "Tên quá dài!",
        "name.regex" => "Tên không đúng định dạng!",
        "sku.min" => "Mã sản phẩm quá ngắn!",
        "sku.max" => "Mã sản phẩm quá dài!",
        "sku.regex" => "Mã sản phẩm không đúng định dạng!",
        "sku.unique" => "Mã sản phẩm đã được sử dụng!",
        "image.required" => "Chưa chọn hình ảnh!",
        "image.max" => "Hình ảnh không được vượt quá 2MB!",
        "image.mimes" => "Tệp hình ảnh phải là tệp jpeg, png, jpg, gif, webp, svg hoặc bmp!",
        "gallery.required" => "Chưa chọn hình ảnh!",
        "gallery.*.max" => "Mỗi tệp hình ảnh không được vượt quá 2MB!",
        "gallery.*.mimes" => "Tệp hình ảnh phải là tệp jpeg, png, jpg, gif, webp, svg hoặc bmp!",
        "required_with" => "Không được bỏ trống!",
        "end_date.after" => "Ngày kết thúc phải là tương lai của ngày bắt đầu!",
        "sell_price.gte" => "Giá bán phải lớn hơn hoặc bằng giá gốc!",
        "discount_price.lt" => "Giá khuyến mãi phải nhỏ hơn giá bán!",
        "endDate.after" => "Ngày kết thúc phải là tương lai của ngày bắt đầu!",
        "sellPrice.gte" => "Giá bán phải lớn hơn hoặc bằng giá gốc!",
        "discountPrice.lt" => "Giá khuyến mãi phải nhỏ hơn giá bán!"
    ];

    public function randomProducts()
    {
        $products = Product::where("is_disabled", 0)->get()->random(5);
        return response()->json([
        "status" => 200,
        "data" => $products,
        "message" => "Get random product successfully."
        ], 200);
    }

    public function getAllProducts()
    {
        $products = Product::all();

        if ($products->count() > 0) {
            return response()->json(
                [
                    "data" => $products,
                ],
                200
            );
        }

        return response()->noContent();
    }

    public function getOneProductById($id) {
        $product = Product::find($id);

        return response()->json(["status" => "ok", "data" => $product], 200);
    }

    public function createOneProduct(Request $request)
    {
        $validator = Validator::make($request->all(), $this->productRules, $this->productMessages);
        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            $newKey = explode(".", $key)[0];
            $errors[$newKey] = $value[0];
        }
        
        $variantsMessages = [];
        if ($request->has('variants')) {
            $variants = json_decode($request->variants);
            foreach ($variants as $variant) {
                $validator = Validator::make((array) $variant, $this->variantRules, $this->productMessages);

                $error = [];
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[$key] = $value[0];
                }
                array_push($variantsMessages, $error);
            }
        }

        if (array_reduce($variantsMessages, function ($pre, $cur) {
            return $pre || count($cur) > 0;
        })) {
             $errors["variants"] = $variantsMessages;
        }
        
        if (count($errors) > 0) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => "Xác thực dữ liệu đầu vào thất bại!",
                    "errors" => $errors,
                ],
                400
            );
        }

        $product = new Product();
        $i = 1;
        $product->name = $request->name;
        while (!!Product::where("name", "like", $product->name)->first()) {
            $product->name = $request->name . $i;
            $i++;
        }

        $product->sku = $request->sku;
        
        $product->slug = $this->create_slug($product->name);
        $product->description = $request->description;
        $product->quantity = $request->quantity;
        $product->origin_price = $request->origin_price;
        $product->sell_price = $request->sell_price;
        
        if ($request->has("discount_price")) {
            $product->discount_price = $request->discount_price;
            $product->start_date = $request->start_date;
            $product->end_date = $request->end_date;
        }

        if ($request->has('category_id')) {
            $product->category_id = $request->category_id;
        }
        $image = Image::make($request->file("image"));
        $image->crop($request->width, $request->height, $request->left, $request->top);

        $filename = time() . '_' . $request->file("image")->getClientOriginalName();
        $parentPath = "images/product/";
        $image->save(public_path($parentPath . $filename));
        $product->image =  $parentPath . $filename ;

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

        if ($request->has('variants')) { 
            $product->variants = $request->variants;
        }

        $product->save();
 
        return response()->json(
            [
                "status" => "ok",
                "data" => $product,
                "message" => "Tạo sản phẩm thành công!"
            ],
            201
        );
    }

    public function toggleOneProduct($id)
    {
        $product = Product::find($id);

        $product->is_disabled = !$product->is_disabled;
        $product->save();

        return response()->json(
            [
                "status" => "ok",
                "message" => "Chuyển đổi trạng thái sản phẩm thành công!"
            ],
            200
        );
    }

    public function updateOneProduct(Request $request, $id)
    {   
        $product = Product::find($id);
        if ($product) {
            $validator = Validator::make($request->all(), [
            "sku" => ["bail", "required", "regex:/^([A-Z0-9]+)$/", "min:2", "max:10", Rule::unique('products')->ignore($product)],
            "name" => ["bail", "required", "regex:/([\p{L}0-9]+)$/u", "min:3", "max:100"],
            "image" => "bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048",
            'gallery.*' => 'bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:2048',
            "description" => "bail|required|string",
            'quantity' => 'bail|required|integer',
            'origin_price' => 'bail|required|numeric',
            'sell_price' => 'bail|required|numeric|gte:origin_price',
            'discount_price' => 'bail|numeric|lt:sell_price|nullable',
            "start_date" => 'bail|required_with:discount_price|date',
            "end_date" => "bail|required_with:discount_price|date|after:start_date",
        ], $this->productMessages);

        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            $newKey = explode(".", $key)[0];
            $errors[$newKey] = $value[0];
        }
        
        $variantsMessages = [];
        if ($request->has('variants')) {
            $variants = json_decode($request->variants);
            foreach ($variants as $variant) {
                $validator = Validator::make((array) $variant, $this->variantRules, $this->productMessages);

                $error = [];
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error[$key] = $value[0];
                }
                array_push($variantsMessages, $error);
            }
        }

        if (array_reduce($variantsMessages, function ($pre, $cur) {
            return $pre || count($cur) > 0;
        })) {
             $errors["variants"] = $variantsMessages;
        }
        
        if (count($errors) > 0) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => "Xác thực dữ liệu đầu vào thất bại!",
                    "errors" => $errors,
                ],
                400
            );
        }

        if ($product->name !== $request->name) {
            $i = 1;
            $product->name = $request->name;
            while (!!Product::where("name", "like", $product->name)->first()) {
                $product->name = $request->name . $i;
                $i++;
            }
        }   
        
        $product->sku = $request->sku;
        
        $product->slug = $this->create_slug($product->name);
        $product->description = $request->description;
        $product->quantity = $request->quantity;
        $product->origin_price = $request->origin_price;
        $product->sell_price = $request->sell_price;
        
        if ($request->has("discount_price")) {
            $product->discount_price = $request->discount_price;
            $product->start_date = $request->start_date;
            $product->end_date = $request->end_date;
        }

        if ($request->has('category_id')) {
            $product->category_id = $request->category_id;
        }

        if ($request->hasFile("image")) {
            if (File::exists($product->image)) {
                File::delete($product->image);
            }

            $image = Image::make($request->file("image"));
            $image->crop($request->width, $request->height, $request->left, $request->top);

            $filename = time() . '_' . $request->file("image")->getClientOriginalName();
            $parentPath = "images/product/";
            $image->save(public_path($parentPath . $filename));
            $product->image =  $parentPath . $filename ;
        }

        if ($request->hasFile("gallery") || $request->has("newGallery")) {
            $oldGalleryArray = explode("|", $product->gallery);
            $oldGallerySet = new \Ds\Set($oldGalleryArray);

            $newGallery = $request->newGallery;
            $newGallerySet = new \Ds\Set(explode("|", $newGallery));
            $diffGallerySet = $oldGallerySet->diff($newGallerySet);
            
            foreach ($diffGallerySet->toArray() as $file) {
                Storage::delete("public/$file");
            }

            $product->gallery = $newGallery;
            if ($request->hasFile("gallery")) {
                foreach ($request->file("gallery") as $index => $image) {
                    if ($index !== 0 || $product->gallery !== "") {
                        $product->gallery .= "|";
                    }
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $parentPath = "images/product/gallery";
                    $imagePath = $image->storeAs($parentPath, $filename, 'public');
                    $product->gallery .= $imagePath;
                }
            }
        }

        if ($request->has('variants')) { 
            $product->variants = $request->variants;
        }

        $product->save();
 
        return response()->json(
            [
                "status" => "ok",
                "message" => "Cập nhật sản phẩm thành công!"
            ],
            200
        );
        }
    }

    public function getOneProductBySlug($slug)
    {
        error_log($slug);
        $product = Product::where('slug', 'like', $slug)->first();
        return response()->json(["status" => "ok", "data" => $product], 200);
    }

    public function getProductPagination() {
        $products = DB::table('products');

        if (request()->query("category_id")) {
            $products = $products->where('category_id', '=', request()->query('category_id'));
        }

        if (request()->query('is_disabled')) {
            $products = $products->where('is_disabled', '=', request()->boolean('is_disabled'));
        }

        if (request()->query("name")) {
            $products = $products->where("name", "like", "%" . request()->query('name') . "%");      
        }

        if (request()->query("per_page")) {
            $products = $products->paginate(request()->query("per_page"));
        }

        return response()->json(["data" => [
            "products" => $products->items(),
            "numberOfPages" => $products->lastPage(),
        ]], 200);
    }

    public function deleteOneProduct($id) {
        $product = Product::find($id);
        if ($product) {
            if (File::exists($product->image)) {
                File::delete($product->image);
            }

            foreach (explode("|", $product->gallery) as $file) {
                Storage::delete("public/$file");
            }
            
            $product->delete();

            return response()->json(["status" => "ok", "message" => "Xoá sản phẩm thành công!"], 200);
        }
    }

    public function getProductsByName($name) {
        $products = Product::where('name', 'like', "%$name%");
        if ($products->count() > 0) {
            return response()->json(["message" => "OK!", "data" => $products], 200);
        }
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

    public function getAllComments($id)
    {
        $product = Product::find($id);
        $comments = $product->comments()->join("users", "users.id", "=" , "product_comments.user_id")
        ->select("product_comments.*", "users.full_name")
        ->get();
        return response()->json([
            "status" => "ok",
            "message" => "success",
            "data" => $comments,
        ], 200);
    }

    public function getAllReviews($id)
    {
        $product = Product::find($id);
        $reviews = $product->reviews()->join("users", "users.id", "=" , "reviews.user_id")
        ->select("reviews.*", "users.full_name")
        ->get();
        return response()->json([
            "message" => "success",
            "data" => $reviews,
        ], 200);
    }

    // sku
    public function generateSku() {
        return response()->json(["data" => $this->generateUniqueCode()], 200);
    }

    public function generateUniqueCode(): string
    {
        do {
            $code = $this->generateRandomCode();
        } while ($this->codeExists($code));

        return $code;
    }

    private function generateRandomCode(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        // $length = random_int(10, 40);
        $length = 10;

        for ($i = 1; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
     
        return $code;
    }

    private function codeExists($code)
    {
        return Product::where('sku', $code)->exists();
    }
}
