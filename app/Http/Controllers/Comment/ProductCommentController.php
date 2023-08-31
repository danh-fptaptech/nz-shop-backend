<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Product_comment;
use Illuminate\Http\Request;

class ProductCommentController extends Controller
{
    public function getAllCommentsProduct() {
        $comments = Product_comment::all();
    
        if ($comments->count() > 0) {
            return response()->json(
                [
                    "data" => $comments,
                    "message" => "Get all comments successfully",
                ],
                200
            );
        }

        return response()->noContent();    
    }

    public function createOneCommentProduct(Request $request) {
        $comment = Product_comment::create($request->all());
        $request->user()->id;
        if ($comment) {
            return response()->json(
                [
                    "data" => $comment,
                    "message" => "Create a comment successfully",
                ],
                201
            );
        }
    }

    public function approveOneCommentProduct($id) {
        $comment = Product_comment::find($id);

        $comment->status = "approved";
        $comment->save();

        return response()->json([
            "message" => "Approved!"
        ], 200);
    }

    public function deleteOneCommentProduct($id) {
        $comment = Product_comment::find($id);

        $comment->status = "deleted";
        $comment->save();

        return response()->json([
            "message" => "Deleted!"
        ], 200);
    }

    public function getAllProductFeedBacksById($id) {
        $feedbacks = Product_comment::find($id)->product_feedbacks;

        return response()->json([
            "message" => "success",
            "data" => $feedbacks,
        ], 200);
    }
}
