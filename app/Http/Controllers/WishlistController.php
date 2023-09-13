<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function getAllWishlists() {
        $wishlist = DB::table('wishlists')
        ->join('users', 'users.id', '=', 'wishlists.user_id')
        ->join('products', 'products.id', '=', 'wishlists.product_id')
        ->select('wishlists.*','products.name','users.full_name')
        ->get();
            
        if ($wishlist->count() > 0) {
            return response()->json(
                [
                    "data" => $wishlist,
                    "message" => "Get all wish successfully",
                ],
                200
            );
        }

          return response()->noContent();      
    }    
    public function getAllWishlistById($id) {
        $wishlist = User::find($id)->wishlists;

        return response()->json([
            "message" => "success",
            "data" => $wishlist,
        ], 200);
    }
    
    public function addOrRemove(Request $request) {
        $wishlist = Wishlist::where('user_id', $request->user_id)->where('product_id', $request->product_id)->first();

        if ($wishlist) {
            $wishlist->delete();

            return response()->json([
            "message" => "DELETe!"
                ], 200); 
        }
        Wishlist::create($request->all());

        return response()->json([
            "message" => "ok!"
        ], 201);
    }

}
