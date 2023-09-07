<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
        public function getAllReviews() {
        $reviews = Review::all();
    
        if ($reviews->count() > 0) {
            return response()->json(
                [
                    "data" => $reviews,
                    "message" => "Get all reviews successfully",
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
    
    public function approveOneReview($id) {
        $reviews = Review::find($id);

        $reviews->status = "approved";
        $reviews->save();

        return response()->json([
            "message" => "Approved!"
        ], 200);
    }

    public function deleteOneReview($id) {
        $reviews = Review::find($id);

        $reviews->status = "deleted";
        $reviews->save();
        $reviews->delete();


        return response()->json([
            "message" => "Deleted!"
        ], 200);
    }


}
