<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function getAllPosts() {
        $posts = Post::all();
    
        if ($posts->count() > 0) {
            return response()->json(
                [
                    "data" => $posts,
                    "message" => "Get all posts successfully",
                ],
                200
            );
        }

        return response()->noContent();    
    }

    
    public function getAllComments($id) {
        $comments = Post::find($id)->comments;

        return response()->json([
            "message" => "success",
            "data" => $comments,
        ], 200);
    }
}
