<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getAllUsers() {
        $users = User::all();
    
        if ($users->count() > 0) {
            return response()->json(
                [
                    "data" => $users,
                    "message" => "Get all users successfully",
                ],
                200
            );
        }

        return response()->noContent();    
    }
}
