<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
  public function randomPost()
  {
    $posts = Post::where("isDeleted", 0)->get()->random(5);
    return response()->json([
      "status" => 200,
      "data" => $posts,
      "message" => "Get random posts successfully."
    ], 200);
  }

  public function index()
  {
    $posts = Post::all();
    if ($posts->count() > 0) {
      return response()->json([
        "status" => 200,
        "data" => $posts,
        "message" => "Get all posts successfully."
      ], 200, []);
    } else {
      return response()->noContent();
    }
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      "title" => "required",
      "author" => "required",
      "image" => "required",
      "description" => "required",
      "content" => "required",
      "type" => "required",
    ]);

    if ($validator->fails()) {
      return response()->json([
        "status" => 400,
        "error" => $validator->messages()
      ], 400);
    } else {
      $post = new Post();
      $image_64 = $request->image;
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'post'.time().'.'.$extension;
                    $storagePath = public_path('images/post/'.$imageName);
                    file_put_contents($storagePath, base64_decode($image));
                    $post->image = 'images/post/'.$imageName;
      $post->title = $request->title;
      $post->author = $request->author;
      $post->description = $request->description;
      $post->content = $request->content;
      $post->type = $request->type;
      $post->save();
      if ($post) {
        return response()->json([
          "status" => 201,
          "data" => $post,
          "message" => "Add new post successfully."
        ], 201);
      } else {
        return response()->json([
          "status" => 500,
          "message" => "Something went wrong !"
        ]);
      }
    }
  }

  public function delete($id)
  {
    $post = Post::find($id);
    if (!$post) {
      return response()->json(
        [
          "status" => 404,
          "message" => "No record found."
        ],
        404
      );
    }
    $post->isDeleted = true;
    $post->save();
    // if ($post->image) {
    //   $destination = public_path($post->image);
    //   if (File::exists($destination)) {
    //     File::delete($destination);
    //   }
    // }
    // $post->delete();
    return response()->json([
      "status" => 200,
      "message" => "Post was deleted successfully."
    ], 200);
  }

  public function getOnePost($id)
  {
    $post = Post::find($id);
    if (!$post) {
      return response()->json([
        "status" => 404,
        "message" => "No record found."
      ], 404);
    } else {
      return response()->json([
        "status" => 200,
        "data" => $post,
        "message" => "Post was found successfully."
      ], 200);
    }
  }

  public function update(Request $request, $id)
  {
    $post = Post::find($id);
    if (!$post) {
      return response()->json([
        "status" => 404,
        "message" => "No record found."
      ], 404);
    }
    $validator = null;
    if ($request->image) {
      $validator = Validator::make($request->all(), [
        "title" => "required",
        "author" => "required",
        "description" => "required",
        "content" => "required",
        "type" => "required",
      ]);
    } else {
      $validator = Validator::make($request->all(), [
        "title" => "required",
        "author" => "required",
        "description" => "required",
        "content" => "required",
        "type" => "required",
      ]);
    }
    if ($validator->fails()) {
      return response()->json(
        [
          "status" => 400,
          'errors' => $validator->errors()
        ],
        400
      );
    } else {
      $post->title = $request->title;
      $post->author = $request->author;
      $post->description = $request->description;
      $post->content = $request->content;
      $post->type = $request->type;

      if($request->image){
        if(File::exists( $post->image)){
          File::delete($post->image);
        }
        
        $image_64 = $request->image;
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'post'.time().'.'.$extension;
                    $storagePath = public_path('images/post/'.$imageName);
                    file_put_contents($storagePath, base64_decode($image));
                    $post->image = 'images/post/'.$imageName;
       }
     
      $post->save();
      if ($post) {
        return response()->json(
          [
            "status" => 200,
            "data" => $post,
            'message' => 'post was updated successfully.'
          ],
          200
        );
      } else {
        return response()->json(
          [
            "status" => 500,
            'message' => 'Error server'
          ],
          500
        );
      }
    }
  }
  public function getAllPosts()
  {
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


  public function getAllComments($id)
    {
        $post = Post::find($id);
        $comments = $post->comments()->join("users", "users.id", "=" , "post_comments.user_id")
        ->select("post_comments.*", "users.full_name")
        ->get();
        return response()->json([
            "status" => "ok",
            "message" => "success",
            "data" => $comments,
        ], 200);
    }
}
