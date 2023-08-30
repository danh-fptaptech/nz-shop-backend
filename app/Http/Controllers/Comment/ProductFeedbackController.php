<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Product_feedback;
use Illuminate\Http\Request;

class ProductFeedbackController extends Controller
{
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
