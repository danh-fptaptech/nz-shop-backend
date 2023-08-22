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
      return response()->json([
        "status" => 404,
        "message" => "No records found."
      ], 404);
    }
  }

  public function store(Request $request)
  {
    error_reporting();
    $validator = Validator::make($request->all(), [
      "title" => "required",
      "author" => "required",
      "image" => "required",
      "image.*" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
      "content" => "bail|required|max:5000",
      "type" => "required",
    ]);

    if ($validator->fails()) {
      return response()->json([
        "status" => 400,
        "error" => $validator->messages()
      ], 400);
    } else {
      $post = new Post();
      $image = $request->file("image");
      $fileName = time() . "." . $image->getClientOriginalName();
      $parentPath = "images/post";
      $destinationPath = public_path($parentPath);
      $imagePath = "$parentPath/$fileName";
      $post->image = $imagePath;
      $post->title = $request->title;
      $post->author = $request->author;
      $post->content = $request->content;
      $post->type = $request->type;
      $post->save();
      $request->image->move($destinationPath, $fileName);
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
      return response()->json([
        "status" => 404,
        "message" => "No record found."
      ], 404);
    }
    if ($post->image) {
      $destination = public_path($post->image);
      if (File::exists($destination)) {
        File::delete($destination);
      }
    }
    $post->delete();
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
        "content" => "bail|required|max:5000",
        "type" => "required",
      ]);
    } else {
      $validator = Validator::make($request->all(), [
        "title" => "required",
        "author" => "required",
        "image" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
        "content" => "bail|required|max:5000",
        "type" => "required",
      ]);
    }
    $post->title = $request->title;
    $post->author = $request->author;
    $post->content = $request->content;
    $post->type = $request->type;
    if ($validator->fails()) {
      return response()->json(
        [
          "status" => 400,
          'errors' => $validator->errors()
        ],
        400
      );
    } else {
      if ($request->hasFile('image')) {
        if ($post->image) {
          $destination = public_path($post->image);
          if (File::exists($destination)) {
            File::delete($destination);
          }
        }
        $image = $request->file("image");
        $fileName = "http://127.0.0.1:8000/images/post/"
          . time() . '.' . $image->getClientOriginalName();
        $image->move(public_path('images'), $fileName);
        $post->image = $fileName;
      }
      $post->save();
      if ($post) {
        return response()->json(
          [
            "status" => 200,
            "data" => $post,
            'message' => 'Post was updated successfully.'
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



  // public function update(Request $request, $id)
  // {
  //   $post = Post::find($id);
  //   if (!$post) {
  //     return response()->json([
  //       "status" => 404,
  //       "message" => "No record found."
  //     ], 404);
  //   }
  //   $validator = null;
  //   if ($request->image) {
  //     $validator = Validator::make($request->all(), [
  //       "title" => "required",
  //       "author" => "required",
  //       "image" => "required",
  //       "image.*" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
  //       "content" => "bail|required|max:5000",
  //       "type" => "required",
  //     ]);
  //   } else {
  //     $validator = Validator::make($request->all(), [
  //       'name' => 'required',
  //       'price' => 'required|numeric',
  //       'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
  //     ]);
  //   }

  //   $post->name = $request->name;
  //   $post->price = $request->price;
  //   if ($validator->fails()) {
  //     return response()->json(
  //       [
  //         "status" => 400,
  //         'errors' => $validator->errors()
  //       ],
  //       400
  //     );
  //   } else {
  //     if ($request->hasFile('image')) {
  //       if ($post->image) {
  //         $destination = public_path($post->image);
  //         if (File::exists($destination)) {
  //           File::delete($destination);
  //         }
  //       }
  //       $image = $request->file('image');
  //       $imageName = "http://127.0.0.1:8000/images/"
  //         . time() . '.' . $image->getClientOriginalName();
  //       $image->move(public_path('images'), $imageName);
  //       $post->image = $imageName;
  //     }
  //     $post->save();
  //     if ($post) {
  //       return response()->json(
  //         [
  //           "status" => 200,
  //           "data" => $post,
  //           'message' => 'Product created successfully'
  //         ],
  //         200
  //       );
  //     } else {
  //       return response()->json(
  //         [
  //           "status" => 500,
  //           'message' => 'Error server!'
  //         ],
  //         500
  //       );
  //     }
  //   }
  // }

  /** Trả về Post View */
  // public function index()
  // {
  //   $posts = Post::all();
  //   return view("post.index", compact("posts"));
  // }


  // public function deletePost($id)
  // {
  //   $post = Post::findOrFail($id);
  //   $imageName = public_path('image/' . $post->image);
  //   $post->delete();
  //   $existedImage = public_path('images' . $imageName);
  //   if (File::exists($existedImage)) {
  //     File::delete($existedImage);
  //   }
  //   return redirect()->route('post.index')
  //     ->with('success', 'Xóa thành công.');
  // }
}
