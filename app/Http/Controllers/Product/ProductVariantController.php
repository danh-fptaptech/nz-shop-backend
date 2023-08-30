<?php

namespace App\Http\Controllers\Product;

use App\Models\Product_variant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductVariantController extends Controller
{
  private $rules = [
              'sku' => "bail|required|string",
              'quantity' => 'bail|required|integer',
              'origin_price' => 'bail|required|numeric|min:1000',
              'sell_price' => 'bail|required|numeric|min:1000',
              'discount_price' => 'bail|numeric|nullable|min:1000',
          ];

  private $messages = [
              "required" => "Không được bỏ trống!",
              "integer" => "Phải là số!",
              "numeric" => "Phải là số!",
              "min" => "Giá phải lớn hơn hoặc bằng 1000 VND",
          ];
  
  public function deleteOneVariant($id) {
    $variant = Product_variant::find($id);
    $variant->is_deleted = true;
    
    $variant->save();

    return response()->json(
      [
        "message" => "Delete successfully!"
      ], 
      200
    );
  }

  public function recoverOneVariant($id) {
    $variant = Product_variant::find($id);
    $product = Product_variant::find($id)->product;

    if ($product->is_deleted) {
      return response()->json(
        [
          "message" => "Pending!",
          "name" => $product->name,
        ],
        202
      );
    }

    $variant->is_deleted = false;
    $variant->save();

    return response()->json(
      [
        "message" => "Recover successfully!",
      ], 
      200
    );
  }

  public function forceRecoverOneVariant($id) {
    $variant = Product_variant::find($id);
    $product = Product_variant::find($id)->product;

    $product->is_deleted = false;
    $product->save();

    $variant->is_deleted = false;
    $variant->save();

    return response()->json(
      [
        "message" => "Recover successfully!",
      ], 
      200
    );
  }

  public function updateOneVariant(Request $request, $id) {
    $validator = Validator::make($request->all(), $this->rules, $this->messages);

    $errors = [];
    foreach ($validator->errors()->messages() as $key => $value) {
      $newKey = explode(".", $key)[0];
      $errors[$newKey] = $value[0];
    }

    if (count($errors) !== 0) {
      return response()->json(
          [
              "errors" => $errors,
          ],
          400
      );
    }
    
    $variant = Product_variant::find($id);
    $variant->fill($request->all())->save();

    return response()->json(
      [
          "message" => "Update successfully!",
      ],
      201
    );
  }

  public function getOneVariantById($id) {
    $variant = Product_variant::find($id);

    return response()->json(["data" => $variant], 200);
  }
}
