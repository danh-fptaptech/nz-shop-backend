<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    //////////////////////////////////////////////////////////////////////////
    //

    public function userStats(): \Illuminate\Http\JsonResponse
    {
        $totalUsers = User::count();
        $today = Carbon::today();
        $new = User::whereDate('created_at', $today)->count();
        $active = User::whereNotNull('email_verified_at')->count();
        $pending = User::whereNull('email_verified_at')->count();
        $status = User::where('status', 'disable')->count();
        return response()->json([
            'total' => $totalUsers,
            'new' => $new,
            'active' => $active,
            'pending' => $pending,
            'status' => $status
        ]);
    }


    //
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    public function getListUser(): \Illuminate\Http\JsonResponse
    {
        $users = User::with('roles:id,name')->get();

        $formattedUsers = $users->map(function ($user) {
            return [
                "id" => $user->id,
                "full_name" => $user->full_name,
                "phone_number" => $user->phone_number,
                "email" => $user->email,
                "role" => $user->roles->implode('name', ', '),
                "isVerify" => $user->email_verified_at ? 'Verified' : 'Pending',
                "status" => $user->status
            ];
        });

        return response()->json($formattedUsers);
    }
//    public function getListUser(Request $request): \Illuminate\Http\JsonResponse
//    {
//        $usersQuery = User::with('roles:id,name');
//        $totalData = $usersQuery->count();
//        $dataGet = $usersQuery->paginate($request->input('dataInPer') ?: 10);
//
//        $formattedUsers = $dataGet->map(function ($user) {
//            return [
//                "id" => $user->id,
//                "full_name" => $user->full_name,
//                "phone_number" => $user->phone_number,
//                "email" => $user->email,
//                "role" => $user->roles->implode('name', ', '),
//                "isVerify" => $user->email_verified_at ? 'Verified' : 'Pending',
//                "status" => $user->status
//            ];
//        });
//        return response()->json(['data'=>$formattedUsers,'totalItems'=>$totalData]);
//    }
    //
    //////////////////////////////////////////////////////////////////////////
    //
    public function getListUserByQuery(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->input('query');
        $users = User::where('status', 'active')
            ->Where('email', 'like', "%$query%")
            ->get(['email']);
        return response()->json($users);
    }

    //
    //////////////////////////////////////////////////////////////////////////
    //

    public function fetchUser(Request $request){
        $user = $request->user();
    }

    //
    //////////////////////////////////////////////////////////////////////////
    //

    // Tâm chèn code

    public function getAllUsers()
    {
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
