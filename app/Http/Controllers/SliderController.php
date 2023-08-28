<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class SliderController extends Controller
{
  public function index()
  {
    $sliders = Slider::all();
    if ($sliders->count() > 0) {
      return response()->json([
        "status" => 200,
        "data" => $sliders,
        "message" => "Get all sliders successfully."
      ], 200);
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
      "name" => "required",
      "title" => "required",
      "image" => "required",
      "image.*" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
    ]);

    if ($validator->fails()) {
      return response()->json([
        "status" => 400,
        "error" => $validator->messages()
      ], 400);
    } else {
      $slider = new Slider();
      $filename = time() . "." . $request->file("image")->getClientOriginalName();
      $parentPath = "images/slider";
      $destinationPath = public_path($parentPath);
      $imagePath = "$parentPath/$filename";
      $slider->name = $request->name;
      $slider->title = $request->title;
      $slider->image = $imagePath;
      $slider->save();
      $request->image->move($destinationPath, $filename);
      if ($slider) {
        return response()->json([
          "status" => 201,
          "data" => $slider,
          "message" => "Add new slider successfully."
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
    $slider = Slider::find($id);
    if (!$slider) {
      return response()->json(
        [
          "status" => 404,
          "message" => "No record found."
        ],
        404
      );
    }
    $slider->isDeleted = true;
    $slider->save();
    return response()->json([
      "status" => 200,
      "message" => "Slider was deleted successfully."
    ], 200);
  }

  public function getOneSlider($id)
  {
    $slider = Slider::find($id);
    if (!$slider) {
      return response()->json([
        "status" => 404,
        "message" => "No record found."
      ], 404);
    } else {
      return response()->json([
        "status" => 200,
        "data" => $slider,
        "message" => "Post was found successfully."
      ], 200);
    }
  }

  public function update(Request $request, $id)
  {
    $slider = Slider::find($id);
    if (!$slider) {
      return response()->json([
        "status" => 404,
        "message" => "No record found."
      ], 404);
    }
    $validator = null;
    if ($request->image) {
      $validator = Validator::make($request->all(), [
        "name" => "required",
        "title" => "required",
        "image" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
      ]);
    } else {
      $validator = Validator::make($request->all(), [
        "name" => "required",
        "title" => "required",
      ]);
    }
    //error_log($request->name);

    //error_log($request->status);
    if ($validator->fails()) {
      return response()->json(
        [
          "status" => 400,
          'errors' => $validator->errors()
        ],
        400
      );
    } else {

      $slider->name = $request->name;
      $slider->title = $request->title;

      if ($request->hasFile('image')) {
        $parentPath = "images/slider";
        $destinationPath = public_path($parentPath);
        $imagePath = "$parentPath/$slider->image";
        if (File::exists($imagePath)) {
          File::delete($imagePath);
        }

        $filename = time() . "." . $request->file("image")->getClientOriginalName();
        $imagePath = "$parentPath/$filename";
        $request->image->move($destinationPath, $filename);
        $slider->image = $imagePath;
      }
      $slider->save();
      if ($slider) {
        return response()->json(
          [
            "status" => 200,
            "data" => $slider,
            'message' => 'Slider was updated successfully.'
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
