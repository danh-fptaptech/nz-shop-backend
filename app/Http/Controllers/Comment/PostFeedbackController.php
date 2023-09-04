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

     public function deleteOneFeedBack($id) {
        $feedback = Post_feedback::find($id);
        $feedback->status = "deleted";
        $feedback->save();

        return response()->json(
            [ 
                "message" => "Delete a feedback successfully",
            ],
            200
        );
    }

       public function approveOneFeedBack($id) {
        $feedback = Post_feedback::find($id);
        $comment = $feedback->post_comment;
        if ($comment->status !== "approved") {
            return response()->json(
                [
                    "message" => "Pending!"
                ],
                202
            );       
        }

        $feedback->status = "approved";
        $feedback->save();

        return response()->json(
            [ 
                "message" => "Approve a feedback successfully",
            ],
            200
        );
    }

    public function approveAllComments($id) {
        $feedback = Post_feedback::find($id);
        $comment = $feedback->post_comment;
        
        $feedback->status = "approved";
        $feedback->save();

        $comment->status = "approved";
        $comment->save();

        return response()->json(
            [ 
                "message" => "Approve successfully",
            ],
            200
        );
    }
}
