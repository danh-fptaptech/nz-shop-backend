<?php

namespace App\Http\Controllers\Description;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DescriptionController extends Controller
{
    private $rules = ["upload" => "bail|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:1800"];
    private $messages = [
        "mimes" => "Hình ảnh phải là tệp jpeg, png, jpg, gif, webp, svg hoặc bmp!",
        "max" => "Hình ảnh không được vượt quá 2MB!"
    ];

    public function storeImageUpload(Request $request) {
        $validator = Validator::make($request->all(), $this->rules, $this->messages);

        $errors = [];
        $newKey = "";
        foreach ($validator->errors()->messages() as $key => $value) {
            $newKey = explode(".", $key)[0];
            $errors[$newKey] = $value[0];
            break;
        }

        if ($newKey) {
            return response()->json(["error" => [ "message" => $errors[$newKey]]] , 400);
        }
        
        $filename = time() . '_' . $request->file("upload")->getClientOriginalName();
        $parentPath = "images/description"; 
        $imagePath = $request->file("upload")->storeAs($parentPath, $filename, 'public');
        
        return response()->json(["url" => url($imagePath)], 200);
    }
}
