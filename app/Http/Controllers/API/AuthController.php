<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required',
                'phone_number' => 'bail|required|string|min:10|max:11|unique:users',
                'email' => 'bail|required|email|unique:users',
                'password' => 'bail|required|string|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()]).{8,20}$/'
            ], [
                'full_name.required' => 'Vui lòng nhập họ và tên',
                'phone_number.required' => 'Vui lòng nhập số điện thoại',
                'phone_number.min' => 'Không đúng định dạng số điện thoại',
                'phone_number.max' => 'Không đúng định dạng số điện thoại',
                'phone_number.unique' => 'Số điện thoại này đã sử dụng ở tài khoản khác',
                'email.required' => 'Vui lòng nhập email',
                'email.email' => 'Email không đúng định dạng',
                'email.unique' => 'Email đã sử dụng đăng ký ở tài khoản khác.',
                'password.required' => 'Password không được để trống',
                'password.regex' => 'Password phải từ 8 -20 ký tự. Ít nhất 1 chữ thường, 1 chữ in hoa và 1 ký tự đặc biệt',
            ]);
            if ($validator->fails()) {
                return response()->json(
                    [
                        "status" => 400,
                        'errors' => $validator->errors()
                    ],
                    400
                );
            }

            $user = User::create([
                'full_name' => $request->input('full_name'),
                'phone_number' => $request->input('phone_number'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password'))
            ]);

            $token = $user->createToken('user_token')->plainTextToken;

            return response()->json(['user' => $user, 'token' => $token], 200);

        } catch
        (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - AuthController.register'
            ]);
        }
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'email' => 'bail|required|string|email',
                'password' => 'bail|required|string|min:8'
            ]);
            $user = User::where('email', '=', $request->input('email'))->firstOrFail();
            if (Hash::check($request->input('password'), $user->password)) {
                $token = $user->createToken('user_token')->plainTextToken;

                return response()->json(['user' => $user, 'token' => $token], 200);
            }else{
                return response()->json(['error' => 'Something went wrong in login']);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - AuthController.login'
            ]);
        }
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        try {
            Auth::user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - AuthController.logout'
            ]);
        }
    }
}
