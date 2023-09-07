<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

use Image;

class GenerateImageController extends Controller
{
    public function generate($width, $height, $background, $text)
    {
        $image = Image::canvas($width, $height, $background);

        // Vẽ văn bản giữa ảnh
        $textSize = min($width, $height) / 10;
        $image->text($text, $width / 2, $height / 2, function ($font) use ($textSize) {
            $font->file(public_path('fonts/OpenSans.ttf'));
            $font->size($textSize);
            $font->color('#000000');
            $font->align('center');
            $font->valign('middle');
        });

        return $image->response('png');
    }
}
