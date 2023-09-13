<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Post_comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostCommentController extends Controller
{
    public function getAllCommentsPost() {
        $comments = DB::table('post_comments')
        ->join('users', 'users.id', '=', 'post_comments.user_id')
        ->join('posts', 'posts.id', '=', 'post_comments.post_id')
        ->leftJoin("post_feedbacks", "post_comments.id", "=", "post_feedbacks.post_comment_id") 
        ->select('post_comments.id', 'post_comments.comment', 'post_comments.is_approved', 'post_comments.created_at',
        'users.full_name', 'posts.title as post_title', DB::raw('count(post_feedbacks.id) as feedback_count'),
        DB::raw('count(CASE WHEN post_feedbacks.is_approved <> 1 THEN 1 END) as pending_feedback_count'))
        ->groupBy('post_comments.id', 'post_comments.comment', 'users.full_name', 'post_title', 
        'post_comments.is_approved', 'post_comments.created_at')
        ->orderBy('post_comments.created_at', 'DESC')
        ->get();
            
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
        $hello = DB::table("a")->where("a", "like", "keyword")->first();
        
        $keywordArr = explode(",", $hello->value);
        $wordArr = explode(" ", $request->comment);
        
        if (count(array_intersect($keywordArr, $wordArr)) > 0) {
            return response()->json([
                    "status" => "error",
                    "message" => "Binh luan chua tu nhay cam!",
                ],
            400);
        }
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

    public function toggleApproveOneCommentPost($id) {
        $comment = Post_comment::find($id);

        $comment->is_approved = !$comment->is_approved;
        $comment->save();

        return response()->json([
            "message" => "Toggle Approved!"
        ], 200);
    }

    public function deleteOneCommentPost($id) {
        $comment = Post_comment::find($id);
 
        $comment->delete();

        return response()->json([
            "message" => "Deleted!"
        ], 200);
    }

    public function getAllPostFeedBacksById($id) {
        $feedbacks = Post_comment::find($id)->post_feedbacks()
        ->join('users', 'users.id', '=', 'post_feedbacks.user_id');

        return response()->json([
            "message" => "success",
            "data" => $feedbacks,
        ], 200);
    }

    public function getUserByCommentId($id) {
        $user = Post_comment::find($id)->user;

        return response()->json(["data" => $user, "message" => "success"], 200); 
    }

    public function getPostByCommentId($id) {
        $post = Post_comment::find($id)->post;

        return response()->json(["data" => $post, "message" => "success"], 200); 
    }

    public function getCommentPagination() {
        $comments = DB::table('post_comments')
        ->join('users', 'users.id', '=', 'post_comments.user_id')
        ->join('posts', 'posts.id', '=', 'post_comments.post_id')
        ->leftJoin("post_feedbacks", "post_comments.id", "=", "post_feedbacks.post_comment_id") 
        ->select('post_comments.id', 'post_comments.comment', 'post_comments.is_approved', 'post_comments.created_at',
        'users.full_name', 'posts.title as post_title', DB::raw('count(post_feedbacks.id) as feedback_count'),
        DB::raw('count(CASE WHEN post_feedbacks.is_approved <> 1 THEN 1 END) as pending_feedback_count'))
        ->groupBy('post_comments.id', 'post_comments.comment', 'users.full_name', 'post_title', 
        'post_comments.is_approved', 'post_comments.created_at')
        ->orderBy('post_comments.created_at', 'DESC');

        if (request()->query('is_approved')) {
            $comments = $comments->where('post_comments.is_approved', '=', request()->boolean('is_approved'));
        }

        if (request()->query("per_page")) {
            $comments = $comments->paginate(request()->query("per_page"));
        }

        return response()->json(["data" => [
            "comments" => $comments->items(),
            "numberOfPages" => $comments->lastPage(),
        ]], 200);
    }

    public function getFeedbackCommentPagination($id) {
        $feedbacks = Post_comment::find($id)->post_feedbacks()
        ->join('users', 'users.id', '=', 'post_feedbacks.user_id')
        ->select('post_feedbacks.*', 'users.full_name')
        ->orderBy('post_feedbacks.created_at', 'DESC');

        if (request()->query('is_approved')) {
            $feedbacks = $feedbacks->where('is_approved', '=', request()->boolean('is_approved'));
        }

        if (request()->query("per_page")) {
            $feedbacks = $feedbacks->paginate(request()->query("per_page"));
        }

        return response()->json(["data" => [
            "feedbacks" => $feedbacks->items(),
            "numberOfPages" => $feedbacks->lastPage(),
        ]], 200);
    }
}
