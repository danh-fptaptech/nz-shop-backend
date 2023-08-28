<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Post_comment;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function getAllComments() {
        $comments = Post_comment::all();
    
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

    public function createOneComment(Request $request) {
        $comment = Post_comment::create($request->all());
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

    public function approveOneComment($id) {
        $comment = Post_comment::find($id);

        $comment->status = "approved";
        $comment->save();

        return response()->json([
            "message" => "Approved!"
        ], 200);
    }

    public function deleteOneComment($id) {
        $comment = Post_comment::find($id);

        $comment->status = "deleted";
        $comment->save();

        return response()->json([
            "message" => "Deleted!"
        ], 200);
    }

    public function getAllPostFeedBacksById($id) {
        $feedbacks = Post_comment::find($id)->post_feedbacks;

        return response()->json([
            "message" => "success",
            "data" => $feedbacks,
        ], 200);
    }
}
