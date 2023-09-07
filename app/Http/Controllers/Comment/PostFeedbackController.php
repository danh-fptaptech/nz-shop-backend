<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Post_feedback;
use Illuminate\Http\Request;

class PostFeedbackController extends Controller
{
    public function createOneFeedBack(Request $request) {
        $feedback = Post_feedback::create($request->all());
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

        public function toggleApproveOneCommentProduct($id) {
        $comment = Post_feedback::find($id);

        $comment->is_approved = !$comment->is_approved;
        $comment->save();

        return response()->json([
            "message" => "Toggle Approved!"
        ], 200);
    }

    public function deleteOneCommentProduct($id) {
        $comment = Post_feedback::find($id);
 
        $comment->delete();

        return response()->json([
            "message" => "Deleted!"
        ], 200);
    }
}
