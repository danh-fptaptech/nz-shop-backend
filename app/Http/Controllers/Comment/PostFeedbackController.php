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
}
