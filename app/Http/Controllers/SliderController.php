<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
  public function index()
  {
    $slider = Slider::all();
    if ($slider->count() > 0) {
      return response()->json([
        "status" => 200,
        "data" => $slider,
        "message" => "Get all sliders successfully."
      ], 200);
    } else {
      return response()->json([
        "status" => 404,
        "message" => "No records found."
      ], 404);
    }
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      "name" => "required",
      "title" => "required",
      "image" => "required",
      "image.*" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
      "status" => "required",
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
      $slider->status = $request->status;
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
    if ($slider->image) {
      $destination = public_path($slider->image);
      if (File::exists($destination)) {
        File::delete($destination);
      }
    }
    $slider->delete();
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
        "status" => "required",
      ]);
    } else {
      $validator = Validator::make($request->all(), [
        "name" => "required",
        "title" => "required",
        "image" => "bail|mimes:jpeg,png,jpg,webp,svg,gif|max:2048",
        "status" => "required",
      ]);
    }
    $slider->name = $request->name;
    $slider->title = $request->title;
    $slider->status = $request->status;
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
        if ($slider->image) {
          $destination = public_path($slider->image);
          if (File::exists($destination)) {
            File::delete($destination);
          }
        }
        $image = $request->file("image");
        $fileName = "http://127.0.0.1:8000/images/slider/"
          . time() . '.' . $image->getClientOriginalName();
        $image->move(public_path('images'), $fileName);
        $slider->image = $fileName;
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
  /** Trả về Slider View */
  // public function index()
  // {
  //   $sliders = Slider::all();
  //   return view("slider.index", compact("sliders"));
  // }

  // // CREATE NEW SLIDER
  // public function create()
  // {
  //   return view("slider.create");
  // }
  // public function store(Request $request)
  // {
  //   $request->validate([
  //     "name" => "required",
  //     "title" => "required",
  //     "image" => "bail|required|image|mimes:jpeg,webp,png,jpg,svg,gif|max:2048",
  //     "date_created" => "required",
  //     "status" => "required",
  //   ], [
  //     "name" => ":atribute Tên ",
  //     "title" => ":atribute Tiêu đề ",
  //     "image" => ":atribute Hình ảnh phải đúng định dạng :mimes",
  //     "date_created" => ":atribute Ngày tạo ",
  //     "status" => ":atribute Tình trạng ",
  //     "required" => ":atribute bắt buộc nhập.",
  //     "max" => ":attribute dung lượng tối đa :max GB",
  //   ]);
  //   $imageName = time() . "." . $request->image->getClientOriginalName();

  //   $slider = new Slider();
  //   $slider->name = $request->name;
  //   $slider->title = $request->title;
  //   $slider->image = $imageName;
  //   $slider->date_created = $request->date_created;
  //   $slider->status = $request->status;
  //   $slider->save();
  //   $request->image->move(public_path("images/"), $imageName);
  //   return redirect()->route("slider.index")
  //     ->with("success", "Thêm mới thành công.");
  // }

  // // EDIT SLIDER
  // public function edit(Slider $slider)
  // {
  //   return view("slider.edit", compact("slider"));
  // }

  // public function update(Request $request, Slider $slider)
  // {
  //   $request->validate([
  //     "name" => "required",
  //     "title" => "required",
  //     "image" => "bail|image|mimes:jpeg,webp,png,jpg,svg,gif|max:2048",
  //     "date_created" => "required",
  //     "status" => "required",
  //   ], [
  //     "name" => ":atribute Tên ",
  //     "title" => ":atribute Tiêu đề ",
  //     "image" => ":atribute Hình ảnh phải đúng định dạng :mimes",
  //     "date_created" => ":atribute Ngày tạo ",
  //     "status" => ":atribute Tình trạng ",
  //     "required" => ":atribute bắt buộc nhập.",
  //     "max" => ":attribute dung lượng tối đa :max GB",
  //   ]);
  //   if ($request->hasFile("image")) {
  //     $existingImage = public_path("images/" . $slider->image);
  //     if (File::exists($existingImage)) {
  //       File::delete($existingImage);
  //     }
  //     $imageName = time() . "." . $request->image->getClientOriginalName();
  //     $request->image->move(public_path("images"), $imageName);
  //     $slider->image = $imageName;
  //   }
  //   $slider->name = $request->name;
  //   $slider->title = $request->title;
  //   $slider->date_created = $request->date_created;
  //   $slider->status = $request->status;
  //   $slider->save();

  //   return redirect()->route("slider.index")
  //     ->with("success", "Cập nhật mới thành công.");
  // }

  // // DELETE SLIDER
  // public function delete($id)
  // {
  //   $slider = Slider::findOrFail($id);
  //   $imageName = public_path('image/' . $slider->image);
  //   $slider->delete();
  //   $existedImage = public_path('images/slider' . $imageName);
  //   if (File::exists($existedImage)) {
  //     File::delete($existedImage);
  //   }
  //   return redirect()->route('slider.index')
  //     ->with('success', 'Xóa thành công.');
  // }
}
