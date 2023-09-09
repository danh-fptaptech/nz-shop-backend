<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function getAllReviews() {
        $reviews = DB::table('reviews')
        ->join('users', 'users.id', '=', 'reviews.user_id')
        ->join('products', 'products.id', '=', 'reviews.product_id')
        ->select('reviews.*','products.name','users.full_name')
        ->get();
            
        if ($reviews->count() > 0) {
            return response()->json(
                [
                    "data" => $reviews,
                    "message" => "Get all comments successfully",
                ],
                200
            );
        }

        return response()->noContent();    
    }
    
    public function createOneReview(Request $request) {
        $reviews = Review::create($request->all());
        if ($reviews) {
            return response()->json(
                [
                    "data" => $reviews,
                    "message" => "Create a reviews successfully",
                ],
                201
            );
        }
    }
    
    public function toggleApproveOneReview($id) {
        $reviews = Review::find($id);

        $reviews->is_approved = !$reviews->is_approved;
        $reviews->save();

        return response()->json([
            "message" => "Approved!"
        ], 200);
    }

    public function deleteOneReview($id) {
        $reviews = Review::find($id);

        $reviews->delete();


        return response()->json([
            "message" => "Deleted!"
        ], 200);
    }

      public function getReviewPagination() {
        $reviews = DB::table('reviews')
        ->join('users', 'users.id', '=', 'reviews.user_id')
        ->join('products', 'products.id', '=', 'reviews.product_id')
        ->select('reviews.*','products.name','users.full_name','products.slug');
        

        if (request()->query('is_approved')) {
            $reviews = $reviews->where('reviews.is_approved', '=', request()->boolean('is_approved'));
        }

        if (request()->query("per_page")) {
            $reviews = $reviews->paginate(request()->query("per_page"));
        }

        return response()->json(["data" => [
            "reviews" => $reviews->items(),
            "numberOfPages" => $reviews->lastPage(),
        ]], 200);
    }

}
