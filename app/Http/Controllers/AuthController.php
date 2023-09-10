<?php

namespace App\Http\Controllers;

use App\Mail\UserCreated;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{

//
///////////////////////////////////          Đăng ký        ///////////////////////////////////////////
//
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'bail|required|regex:/([\p{L} ]+)$/u|min:2|max:150',
                'phone_number' => 'bail|required|string|regex:/^[0-9]{10}$/|unique:users',
                'email' => 'bail|required|email|unique:users',
                'password' => 'bail|required|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()]).{8,20}$/'
            ], [
                'full_name.required' => 'Vui lòng nhập họ và tên',
                'full_name.regex' => 'Họ và tên chỉ bao gồm chữ cái',
                'full_name.min' => 'Họ và tên phải từ 2 ký tự trở lên',
                'full_name.max' => 'Họ và tên không vượt quá 150 ký tự',
                'phone_number.required' => 'Vui lòng nhập số điện thoại',
                'phone_number.regex' => 'Không đúng định dạng số điện thoại',
                'phone_number.unique' => 'Số điện thoại này đã sử dụng ở tài khoản khác',
                'email.required' => 'Vui lòng nhập email',
                'email.email' => 'Email không đúng định dạng',
                'email.unique' => 'Email đã sử dụng đăng ký ở tài khoản khác.',
                'password.required' => 'Password không được để trống',
                'password.regex' => 'Password phải từ 8 -20 ký tự. Ít nhất 1 chữ thường, 1 chữ in hoa và 1 ký tự đặc biệt',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ], 400);
            }

            $user = User::create([
                'full_name' => $request->input('full_name'),
                'phone_number' => $request->input('phone_number'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password'))
            ]);
            event(new Registered($user));
            $token = $user->createToken('user_token')->plainTextToken;
            $user->syncRoles(Role::where('name', 'User')->first());
            return response()->json([
                'status' => "ok", 'message' => "Đăng ký thành công!", 'user_id' => $user->id, 'token' => $token
            ]);

        } catch
        (\Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => substr($e->getMessage(), 0, 150),
            ]);
        }
    }

//
//////////////////////////////////   Xác nhận tài khoản    ///////////////////////////////////////////
//
    public function verify(Request $request): JsonResponse
    {
        $queryString = Crypt::decryptString($request->input('key'));
        parse_str($queryString, $params);
        $dataArray = [
            'id' => $params['id'],
            'hash' => $params['hash'],
            'exp' => $params['exp'],
        ];
        $expTimestamp = strtotime($dataArray['exp']);
        if ($expTimestamp < now()->timestamp) {
            return response()->json([
                'status' => "error", 'message' => "Link xác nhận đã hết hạn!",
            ]);
        }
        $user = User::findOrFail($dataArray['id']);

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => "error", 'message' => "Tài khoản này đã xác nhận rồi!", 'isSuccess' => true
            ]);
        } else {
            if (!hash_equals((string) $dataArray['hash'], sha1($user->getEmailForVerification()))) {
                return response()->json([
                    'status' => "error", 'message' => "Mã xác nhận không chính xác!", 'isSuccess' => false,
                ]);
            } else {
                if ($user->markEmailAsVerified()) {
                    event(new Verified($user));
                    return response()->json([
                        'status' => "ok", 'message' => "Xác nhận tài khoản thành công!", 'isSuccess' => true
                    ]);
                } else {
                    return response()->json([
                        'status' => "error", 'message' => "Quá trình xác nhận thất bại!", 'isSuccess' => false
                    ]);
                }
            }
        }
    }

//
////////////////////////////////    Gửi lại email xác nhận    ////////////////////////////////////////
//

    public function reSentVerify(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['status' => "error", 'message' => "Tài khoản này đã xác nhận rồi!"]);
        } else {
            $request->user()->sendEmailVerificationNotification();
            return response()->json(['status' => "ok", 'message' => "Đã gửi email xác nhận!"]);
        }
    }

//
////////////////////////////////       Xử lý đăng nhập       /////////////////////////////////////////
//
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'email' => 'bail|required|email',
                'password' => 'bail|required|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()]).{8,20}$/'
            ]);
            $user = User::where('email', '=', $request->input('email'))->firstOrFail();
            if ($user->status !== 'active') {
                return response()->json(['status' => "error", 'message' => "Tài khoản đã bị khoá."]);
            }
            if (Hash::check($request->input('password'), $user->password)) {
                $token = $user->createToken('user_token')->plainTextToken;

                return response()->json([
                    'status' => "ok", 'message' => "Đăng nhập thành công", 'user_id' => $user->id, 'token' => $token
                ]);
            } else {
                return response()->json(['status' => "error", 'message' => "Sai mật khẩu!"]);
            }
        } catch
        (\Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => substr($e->getMessage(), 0, 150),
            ]);
        }
    }

//
/////////////////////////////////       Xử lý đăng xuất      /////////////////////////////////////////
//
    public function logout(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user) {
                $user->currentAccessToken()->delete();
                return response()->json([
                    'status' => "ok", 'message' => "Đăng xuất thành công", 'isSuccess' => true
                ]);
            } else {
                return response()->json([
                    'status' => "error", 'message' => "Không tìm thấy dữ liệu"
                ]);
            }
        } catch
        (\Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => substr($e->getMessage(), 0, 150),
            ]);
        }
    }
//
//////////////////////////////// Xử lý quên mật khẩu -> gửi email //////////////////////////////////////
//
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => 'bail|required|email']);
            $status = Password::sendResetLink($request->only('email'));
            if ($status === Password::INVALID_USER) {
                return response()->json(['status' => "error", 'message' => 'Không tìm thấy tài khoản này']);
            } elseif ($status === Password::RESET_LINK_SENT) {
                return response()->json(['status' => "ok", 'message' => "Đã gửi email khôi phục mật khẩu"]);
            } else {
                return response()->json(['status' => "error", 'message' => 'Quá trình gửi email thất bại'], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => substr($e->getMessage(), 0, 150),
            ]);
        }
    }
//
////////////////////////////   Xử lý xác nhận token -> đặt lại password    ///////////////////////////////
//
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'key' => 'required',
                'password' => 'bail|required|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()]).{8,20}$/'
            ], [
                'password.required' => 'Password không được để trống',
                'password.regex' => 'Password phải từ 8 -20 ký tự. Ít nhất 1 chữ thường, 1 chữ in hoa và 1 ký tự đặc biệt',
            ]);
            $queryString = Crypt::decryptString($request->input('key'));
            parse_str($queryString, $params);
            $dataArray = [
                'email' => $params['email'],
                'token' => $params['token'],
                'exp' => $params['exp'],
            ];
            $expTimestamp = strtotime($dataArray['exp']);
            if ($expTimestamp < now()->timestamp) {
                return response()->json([
                    'status' => "error", 'message' => "Link xác nhận đã hết hạn!",
                ]);
            }
            $user = User::where('email', '=', $dataArray['email'])->firstOrFail();
            if (Hash::check($request->input('password'), $user->password)) {
                return response()->json(['status' => "error", 'message' => "Bạn đã nhập trùng với mật khẩu cũ"]);
            } else {
                $status = Password::reset(
                    [
                        'email' => $dataArray['email'],
                        'password' => $request->input('password'),
                        'token' => $dataArray['token']
                    ],
                    function ($user, $password) {
                        $user->forceFill(['password' => bcrypt($password)])->save();
                        event(new PasswordReset($user));
                    }
                );
                return $status == Password::PASSWORD_RESET
                    ? response()->json(['status' => "ok", 'message' => "Đổi mật khẩu thành công", 'isSuccess' => true])
                    : response()->json(['status' => "error", 'message' => "Xác thực không thành công"]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => substr($e->getMessage(), 0, 150),
            ]);
        }
    }
//
//////////////////////////////            Kiểm tra trạng thái Admin       //////////////////////////////
//

    public function isAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            return ((Auth::user()->hasRole(1) || Auth::user()->hasRole(1))
                ? response()->json(['isAdmin' => true])
                : response()->json(['isAdmin' => false])
            );
        } else {
            return response()->json(['isAdmin' => false]);
        }
    }

//
//////////////////////////////        Kiểm tra trạng thái Đăng nhập     ///////////////////////////////////
//

    public function isLogin(): \Illuminate\Http\JsonResponse
    {
        if (Auth::guard('api')->check()) {
            return response()->json(['isLogin' => true]);
        } else {
            return response()->json(['isLogin' => false]);
        }
    }

//
////////////////////////////////    Tạo tài khoản trực tiếp        //////////////////////////////////
//
    public function createUser(Request $request): \Illuminate\Http\JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'full_name' => 'bail|required|regex:/([\p{L} ]+)$/u|min:2|max:150',
                'phone_number' => 'bail|required|string|min:10|max:11|unique:users',
                'email' => 'bail|required|email|unique:users',
            ], [
                'full_name.required' => 'Vui lòng nhập họ và tên',
                'full_name.regex' => 'Họ và tên chỉ bao gồm chữ cái',
                'full_name.min' => 'Họ và tên phải từ 2 ký tự trở lên',
                'full_name.max' => 'Họ và tên không vượt quá 150 ký tự',
                'phone_number.required' => 'Vui lòng nhập số điện thoại',
                'phone_number.min' => 'Không đúng định dạng số điện thoại',
                'phone_number.max' => 'Không đúng định dạng số điện thoại',
                'phone_number.unique' => 'Số điện thoại này đã sử dụng ở tài khoản khác',
                'email.required' => 'Vui lòng nhập email',
                'email.email' => 'Email không đúng định dạng',
                'email.unique' => 'Email đã sử dụng đăng ký ở tài khoản khác.',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ]);
            }

            $user = new User([
                'full_name' => $request->input('full_name'),
                'phone_number' => $request->input('phone_number'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password'))
            ]);

            $user->save();
            if ($request->input('role')) {
                $role = Role::where('name', $request->input('role'))->first();
                $user->syncRoles($role);
            }
            if ($request->input('verify') == "verified") {
                $user->markEmailAsVerified();
            }
            Mail::to($user->email)->send(new UserCreated($request->input('email'), $request->input('password')));
            return response()->json(['status' => 'ok', 'message' => 'Tạo tài khoản thành công']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }
//
////////////////////////////////    Lấy tài khoản theo ID      //////////////////////////////////
//

    public function infoUserID($id): \Illuminate\Http\JsonResponse
    {
        $user = User::with('roles:id,name')->find($id);
        if ($user) {
            $formattedUser = [
                "id" => $user->id,
                "full_name" => $user->full_name,
                "phone_number" => $user->phone_number,
                "email" => $user->email,
                "role" => $user->roles->implode('name', ', '),
                "verified" => $user->email_verified_at ? 'Verified' : 'Pending',
                "status" => $user->status
            ];

            return response()->json($formattedUser);
        } else {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản.']);
        }
    }


//
////////////////////////////////    Edit thông tin tài khoản        //////////////////////////////////
//
    public function updateUser(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $user = User::find($id);
            if ($user) {
                $validator = Validator::make($request->all(), [
                    'full_name' => 'bail|required|regex:/([\p{L} ]+)$/u|min:2|max:150',
                    'phone_number' => [
                        'bail', 'required', 'string', 'min:10', 'max:11', Rule::unique('users')->ignore($user->id)
                    ],
                    'email' => ['bail', 'required', 'email', Rule::unique('users')->ignore($user->id)]
                ], [
                    'full_name.required' => 'Vui lòng nhập họ và tên',
                    'full_name.regex' => 'Họ và tên chỉ bao gồm chữ cái',
                    'full_name.min' => 'Họ và tên phải từ 2 ký tự trở lên',
                    'full_name.max' => 'Họ và tên không vượt quá 150 ký tự',
                    'phone_number.required' => 'Vui lòng nhập số điện thoại',
                    'phone_number.min' => 'Không đúng định dạng số điện thoại',
                    'phone_number.max' => 'Không đúng định dạng số điện thoại',
                    'phone_number.unique' => 'Số điện thoại này đã sử dụng ở tài khoản khác',
                    'email.required' => 'Vui lòng nhập email',
                    'email.email' => 'Email không đúng định dạng',
                    'email.unique' => 'Email đã sử dụng đăng ký ở tài khoản khác.',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                    ]);
                }

                $user->full_name = $request->input('full_name');
                $user->phone_number = $request->input('phone_number');
                $user->email = $request->input('email');
                $user->save();
                if ($request->input('role')) {
                    $role = Role::where('name', $request->input('role'))->first();
                    $user->syncRoles($role);
                }
                if ($request->input('verify') == "verified") {
                    $user->markEmailAsVerified();
                } else {
                    $user->email_verified_at = null;
                    $user->save();
                }
                return response()->json(['status' => 'ok', 'message' => 'Cập nhật tài khoản thành công']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy tài khoản']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }
//
////////////////////////////////    Thay đổi trạng thái tài khoản      //////////////////////////////////
//
    public function changeStatusUser($id): \Illuminate\Http\JsonResponse
    {
        $user = User::find($id);
        if ($user) {
            if ($user->status === 'active') {
                $user->tokens()->delete();
            }
            $user->status = $user->status === 'active' ? 'disable' : 'active';
            $user->save();
            return response()->json(['status' => 'ok', 'message' => 'Cập nhật thành công']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy tài khoản']);
        }
    }
}

