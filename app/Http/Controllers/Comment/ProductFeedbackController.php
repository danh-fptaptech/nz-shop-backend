<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Product_feedback;
use Illuminate\Http\Request;

class ProductFeedbackController extends Controller
{

    public function toggleApproveOneCommentProduct($id) {
        $comment = Product_feedback::find($id);

        $comment->is_approved = !$comment->is_approved;
        $comment->save();

        return response()->json([
            "message" => "Toggle Approved!"
        ], 200);
    }

    public function deleteOneCommentProduct($id) {
        $comment = Product_feedback::find($id);
 
        $comment->delete();

        return response()->json([
            "message" => "Deleted!"
        ], 200);
    }

    public function createOneFeedBack(Request $request) {
        $feedback = Product_feedback::create($request->all());
        if ($feedback) {
            return response()->json(
                [
                    "data" => $feedback,
                    "message" => "Create a feedback successfully",
                ],
                201
            );
        }
    }
}
