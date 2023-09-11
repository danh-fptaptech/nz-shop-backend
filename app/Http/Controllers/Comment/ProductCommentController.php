<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Post_comment;
use App\Models\Post_feedback;
use App\Models\Product_comment;
use App\Models\Product_feedback;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCommentController extends Controller
{
    public function getAllCommentsProduct() {
        $comments = DB::table('product_comments')
        ->join('users', 'users.id', '=', 'product_comments.user_id')
        ->join('products', 'products.id', '=', 'product_comments.product_id')
        ->leftJoin("product_feedbacks", "product_comments.id", "=", "product_feedbacks.product_comment_id") 
        ->select('product_comments.id', 'product_comments.comment', 'product_comments.is_approved', 'product_comments.created_at',
        'users.full_name', 'products.name as product_name', DB::raw('count(*) as feedback_count'),  
        DB::raw('count(CASE WHEN product_feedbacks.is_approved <> 1 THEN 1 END) as pending_feedback_count'))
        ->groupBy('product_comments.id', 'product_comments.comment', 'users.full_name', 'product_name', 
        'product_comments.is_approved', 'product_comments.created_at')
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

    public function createOneCommentProduct(Request $request) {
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

        $comment = Product_comment::create($request->all());
        error_log($request);
        // $request->user()->id;
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

    public function toggleApproveOneCommentProduct($id) {
        $comment = Product_comment::find($id);

        $comment->is_approved = !$comment->is_approved;
        $comment->save();

        return response()->json([
            "message" => "Toggle Approved!"
        ], 200);
    }

    public function deleteOneCommentProduct($id) {
        $comment = Product_comment::find($id);
 
        $comment->delete();

        return response()->json([
            "message" => "Deleted!"
        ], 200);
    }

    public function getAllProductFeedBacksById($id) {
        $feedbacks = Product_comment::find($id)->product_feedbacks()
        ->join('users', 'users.id', '=', 'product_feedbacks.user_id');

        return response()->json([
            "message" => "success",
            "data" => $feedbacks,
        ], 200);
    }

    public function getUserByCommentId($id) {
        $user = Product_comment::find($id)->user;

        return response()->json(["data" => $user, "message" => "success"], 200); 
    }

    public function getProductByCommentId($id) {
        $product = Product_comment::find($id)->product;

        return response()->json(["data" => $product, "message" => "success"], 200); 
    }

    public function commentToday() {
        $today = Carbon::today();
        $productcommenttoday = Product_comment::whereDate('created_at', $today)->count();
        $productfeedbackstoday =Product_feedback::whereDate('created_at', $today)->count();

        $postcommenttoday = Post_comment::whereDate('created_at', $today)->count();
        $postfeedbackstoday =Post_feedback::whereDate('created_at', $today)->count();

        $reivewtoday = Review::whereDate('created_at', $today)->count();

        return response()->json(["todayPostComment" => $postcommenttoday + $postfeedbackstoday,"todayProductComment" => $productcommenttoday +  $productfeedbackstoday, "todayReview"=> $reivewtoday ,"message" => "success"], 200); 
    }
    

    public function getCommentPagination() {
        $comments = DB::table('product_comments')
        ->join('users', 'users.id', '=', 'product_comments.user_id')
        ->join('products', 'products.id', '=', 'product_comments.product_id')
        ->leftJoin("product_feedbacks", "product_comments.id", "=", "product_feedbacks.product_comment_id") 
        ->select('products.slug','product_comments.id', 'product_comments.comment', 'product_comments.is_approved', 'product_comments.created_at',
        'users.full_name', 'products.name as product_name', DB::raw('count(product_feedbacks.id) as feedback_count'),
        DB::raw('count(CASE WHEN product_feedbacks.is_approved <> 1 THEN 1 END) as pending_feedback_count'))
        ->groupBy('products.slug','product_comments.id', 'product_comments.comment', 'users.full_name', 'product_name', 
        'product_comments.is_approved', 'product_comments.created_at')->orderBy('product_comments.created_at', 'DESC');
        

        if (request()->query('is_approved')) {
            $comments = $comments->where('product_comments.is_approved', '=', request()->boolean('is_approved'));
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
        $feedbacks = Product_comment::find($id)->product_feedbacks()
        ->join('users', 'users.id', '=', 'product_feedbacks.user_id')
        ->select('product_feedbacks.*', 'users.full_name')
        ->orderBy('product_feedbacks.created_at', 'DESC');

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
