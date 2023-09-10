<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function getGoogleSignInUrl($provider)
    {
        try {
            $url = Socialite::driver($provider)->stateless()
                ->redirect()->getTargetUrl();
            return response()->json([
                'url' => $url,
            ], 200);
        } catch (\Exception $exception) {
            return $exception;
        }
    }

    public function loginCallback(Request $request, $provider)
    {
        $s_user = Socialite::with($request->provider)->stateless()->userFromToken($request->access_token);
        return response()->json(["token" => $s_user], 200);
        // try {

        //     // $state = $request->input('state');

        //     // parse_str($state, $result);
        //     $googleUser = Socialite::driver($provider)->stateless()->user();
        //     // dd($googleUser);
        //     $user = User::where('email', $googleUser->email)->first();
        //     if ($user) {
        //         return response()->json([
        //           'status' => 'google sign in successfully',
        //           'data' => $user,
        //         ], 200);
        //     }
        //     $user = User::create(
        //         [
        //             'email' => $googleUser->email,
        //             'full_name' => $googleUser->name,
        //             'phone_number' => 12345,
        //             'password' => 'hello',
        //         ]
        //     );
            
        //     if ($user) {
        //       $user->email_verified_at = $user->created_at;
        //       $user->save();
        //     }

        //     return response()->json([
        //         'status' => 'create google sign in successfully',
        //         'data' => $user,
        //     ], 201);

        // } catch (\Exception $exception) {
        //     return response()->json([
        //         'status' => 'google sign in failed',
        //         'error' => $exception,
        //         'message' => $exception->getMessage()
        //     ], Response::HTTP_BAD_REQUEST);
        // }
    }

    public function loginCallback1(Request $request)
    {
        try {
            dd("a");    
            $s_user = Socialite::with($request->provider)->stateless()->userFromToken($request->access_token);
            return response()->json(["data" => $s_user], 200);
            // dd($s_user);
            // $googleUser = Socialite::driver('google')->stateless()->user();
            // // dd($googleUser);
            // $user = User::where('email', $googleUser->email)->first();
            // if ($user) {
            //     return response()->json([
            //       'status' => 'google sign in successfully',
            //       'data' => $user,
            //     ], 200);
            // }
            // $user = User::create(
            //     [
            //         'email' => $googleUser->email,
            //         'full_name' => $googleUser->name,
            //         'phone_number' => 12345,
            //         'password' => 'hello',
            //     ]
            // );
            
            // if ($user) {
            //   $user->email_verified_at = $user->created_at;
            //   $user->save();
            // }

            // return response()->json([
            //     'status' => 'create google sign in successfully',
            //     'data' => $user,
            // ], 201);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'google sign in failed',
                'error' => $exception,
                'message' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}