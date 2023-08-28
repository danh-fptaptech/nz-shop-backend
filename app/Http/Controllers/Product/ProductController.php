<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getAllProducts() {
        $products = Product::all();
    
        if ($products->count() > 0) {
            return response()->json(
                [
                    "data" => $products,
                    "message" => "Get all products successfully",
                ],
                200
            );
        }

        return response()->noContent();    
    }

    public function getAllComments($id) {
        $comments = Product::find($id)->comments;

        return response()->json([
            "message" => "success",
            "data" => $comments,
        ], 200);
    }

    public function getAllReviews($id) {
        $reviews = Product::find($id)->reviews;

        return response()->json([
            "message" => "success",
            "data" => $reviews,
        ], 200);
    }
}
