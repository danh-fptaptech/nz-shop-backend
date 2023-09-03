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
    ]);

    if ($validator->fails()) {
      return response()->json([
        "status" => 400,
        "error" => $validator->messages()
      ], 400);
    } else {
      $slider = new Slider();
      $image_64 = $request->image;
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'slider'.time().'.'.$extension;
                    $storagePath = public_path('images/slider/'.$imageName);
                    file_put_contents($storagePath, base64_decode($image));
                    $slider->image = 'images/slider/'.$imageName;
      $slider->name = $request->name;
      $slider->title = $request->title;
      $slider->save();
      // $request->image->move($storagePath, $imageName);
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
      ]);
    } else {
      $validator = Validator::make($request->all(), [
        "name" => "required",
        "title" => "required",
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
      $slider->name = $request->name;
      $slider->title = $request->title;
      $image_64 = $request->image;
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'slider'.time().'.'.$extension;
                    $storagePath = public_path('images/slider/'.$imageName);
                    file_put_contents($storagePath, base64_decode($image));
                    $slider->image = 'images/slider/'.$imageName;
      if ($request->hasFile('image')) {
        $storagePath = public_path('images/slider/'.$imageName);
        if (File::exists($storagePath)) {
          File::delete($storagePath);
        }  
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
