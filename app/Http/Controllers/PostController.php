<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
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
    return response()->json([
      "status" => 404,
      "message" => "No records found."
    ], 404);
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      "title" => "required",
      "author" => "required",
      "image" => "required",
      "image.*" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
      "content" => "bail|required|max:10000",
      "type" => "required",
    ]);

    if ($validator->fails()) {
      return response()->json([
        "status" => 400,
        "error" => $validator->messages()
      ], 400);
    } else {
      $post = new Post();
      $filename = time() . "." . $request->file("image")->getClientOriginalName();
      $parentPath = "images/post";
      $destinationPath = public_path($parentPath);
      $imagePath = "$parentPath/$filename";
      $post->title = $request->title;
      $post->author = $request->author;
      $post->content = $request->content;
      $post->type = $request->type;
      $post->image = $imagePath;
      $post->save();
      $request->image->move($destinationPath, $filename);
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

  public function getPost($title)
  {
    $post = Post::find($title);
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
        "image.*" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
        "content" => "bail|required|max:10000",
        "type" => "required",
      ]);
    } else {
      $validator = Validator::make($request->all(), [
        "title" => "required",
        "author" => "required",
        "content" => "bail|required|max:10000",
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
      $post->content = $request->content;
      $post->type = $request->type;

      if ($request->hasFile('image')) {
        $parentPath = "images/post";
        $destinationPath = public_path($parentPath);
        $imagePath = "$parentPath/$post->image";
        if (File::exists($imagePath)) {
          File::delete($imagePath);
        }

        $filename = time() . "." . $request->file("image")->getClientOriginalName();
        $imagePath = "$parentPath/$filename";
        $request->image->move($destinationPath, $filename);
        $post->image = $imagePath;
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

}
