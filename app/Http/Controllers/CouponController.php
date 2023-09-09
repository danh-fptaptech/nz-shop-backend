<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{

//
//////            Generate Coupon
//
    /**
     * @throws \Exception
     */
    public function generateUniqueCode(): string
    {
        do {
            $code = $this->generateRandomCode();
        } while ($this->codeExists($code));

        return $code;
    }

    /**
     * @throws \Exception
     */
    private function generateRandomCode(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        $length = random_int(10, 40);

        for ($i = 1; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
            if ($i % 5 === 0) {
                $code .= '-';
                $i++;
            }
        }
        if (str_ends_with($code, '-')) {
            $code = substr($code, 0, -1);
        }
        return $code;
    }

    private function codeExists($code)
    {
        return Coupon::where('code', $code)->exists();
    }

//
//////            Create Coupon
//

    public function createCoupon(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!auth()->user()->can('Create Coupon')) {
            return response()->json(['status' => 'error', 'message' => 'Bạn không có quyền truy cập chức năng này.'],
                403);
        }
        $data = $request->all();
//        dd($data);
        try {
            $validator = Validator::make($data, [
                'name' => 'bail|required|unique:coupons|regex:/^([\p{L}0-9#&\-_ ]{2,100})$/u',
                'code' => 'bail|required|unique:coupons|regex:/^[0-9a-zA-Z\-]{5,50}$/',
                'type_coupon' => ['bail', 'required', 'regex:/^(shipping|totalcart|onproduct)$/'],
                'value' => ['bail', 'required', ' numeric', 'min:0'],
                'type_value' => [
                    'bail', 'required', 'regex:/^(reduce_shipping|free_shipping|number_value|percent_value)$/'
                ],
            ], [
                'name.required' => 'Tên không được để trống.',
                'name.regex' => 'Tên không đúng định dạng. Độ dài 2-100 ký tự gồm chữ và số',
                'name.unique' => 'Tên đã tồn tại.',
                'code.required' => 'Code không được để trống.',
                'code.unique' => 'Code đã tồn tại.',
                'code.regex' => 'Code không đúng định dạng.Độ dài 5-50 ký tự gồm chữ và số',
                'type_coupon.required' => 'Loại coupon không được để trống.',
                'type_coupon.regex' => 'Loại coupon không đúng.',
                'value.required' => 'Giá trị coupon không được để trống.',
                'value.numeric' => 'Giá trị coupon phải là số.',
                'value.min' => 'Giá trị coupon phải là số lớn hơn hoặc bằng 0.',
                'value.max' => 'Không thể vượt quá 100%.',
                'type_value.required' => 'Loại giá trị không được để trống.',
                'type_value.regex' => 'Loại giá trị không đúng.',
            ]);
            $validator->sometimes('value', 'required|numeric|min:0|max:100', function ($input) {
                return $input->type_value === 'percent_value';
            });
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ]);
            }

            $coupon = Coupon::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'type_coupon' => $data['type_coupon'],
                'value' => $data['value'],
                'type_value' => $data['type_value'],
            ]);
            $coupon->products_id = $data['products_id'] ?? null;
            $coupon->limit_time = $data['limit_time'] ?? null; //isset($data['key']) ? $data['key'] : null;
            $coupon->date_start = $data['date_start'] ?? null;
            $coupon->date_end = $data['date_end'] ?? null;
            $coupon->status = $data['status'] ?? 'active';
            $coupon->coupon_requests = $data['coupon_requests'] ?? null;
            $coupon->save();

            return response()->json(['status' => 'ok', 'message' => 'Tạo coupon thành công!']);

        } catch
        (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }

//
//////            Get List Coupon
//

    public function getListCoupon(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Coupon::all());
    }

//
//////            Create Coupon
//
    public function changeStatusCoupon($id): \Illuminate\Http\JsonResponse
    {
        $coupon = Coupon::find($id);
        if ($coupon) {
            $coupon->status = $coupon->status === 'active' ? 'disable' : 'active';
            $coupon->save();
            return response()->json(['status' => 'ok', 'message' => 'Cập nhật thành công']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy tài khoản']);
        }
    }

//
//
    public function deleteCoupon(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Check permission
            if (!auth()->user()->can('Delete Coupon')) {
                return response()->json([
                    'status' => 'error', 'message' => 'Bạn không có quyền truy cập chức năng này.'
                ]);
            }
            // Check validator
            $validator = Validator::make($request->all(), [
                'coupon_id' => 'required|numeric|exists:coupons,id',
            ], [
                'coupon_id.required' => 'Thiếu dữ liệu',
                'coupon_id.numeric' => 'Sai kiểu dữ liệu',
                'coupon_id.exists' => 'Dữ liệu không tồn tại'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ]);
            }
            // Action
            $coupon = Coupon::where('id', $request->input('coupon_id'))->first();
            $coupon->delete();
            return response()->json(['status' => 'ok', 'message' => 'Xoá mã giảm giá thành công']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function updateCoupon(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        // Check permission
        if (!auth()->user()->can('Edit Coupon')) {
            return response()->json(['status' => 'error', 'message' => 'Bạn không có quyền truy cập chức năng.']);
        }

        $data = $request->all();

        $coupon = Coupon::find($id);
        if ($coupon) {
            try {
                $validator = Validator::make($data, [
                    'name' => [
                        'bail', 'required', 'regex:/^([\p{L}0-9#&\-_ ]{2,100})$/u',
                        Rule::unique('coupons')->ignore($coupon->id)
                    ],
                    'code' => [
                        'bail', 'required', 'regex:/^[0-9a-zA-Z\-]{5,50}$/',
                        Rule::unique('coupons')->ignore($coupon->id)
                    ],
                    'type_coupon' => ['bail', 'required', 'regex:/^(shipping|totalcart|onproduct)$/'],
                    'value' => ['bail', 'required', ' numeric', 'min:0'],
                    'type_value' => [
                        'bail', 'required', 'regex:/^(reduce_shipping|free_shipping|number_value|percent_value)$/'
                    ],
                ], [
                    'name.required' => 'Tên không được để trống.',
                    'name.regex' => 'Tên không đúng định dạng. Độ dài 2-100 ký tự gồm chữ và số',
                    'name.unique' => 'Tên đã tồn tại.',
                    'code.required' => 'Code không được để trống.',
                    'code.unique' => 'Code đã tồn tại.',
                    'code.regex' => 'Code không đúng định dạng.Độ dài 5-50 ký tự gồm chữ và số',
                    'type_coupon.required' => 'Loại coupon không được để trống.',
                    'type_coupon.regex' => 'Loại coupon không đúng.',
                    'value.required' => 'Giá trị coupon không được để trống.',
                    'value.numeric' => 'Giá trị coupon phải là số.',
                    'value.min' => 'Giá trị coupon phải là số lớn hơn hoặc bằng 0.',
                    'value.max' => 'Giá trị coupon phải là số nhỏ hơn hoặc bằng 100.',
                    'type_value.required' => 'Loại giá trị không được để trống.',
                    'type_value.regex' => 'Loại giá trị không đúng.',
                ]);
                $validator->sometimes('value', 'required|numeric|min:0|max:100', function ($input) {
                    return $input->type_value === 'percent_value';
                });
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                    ]);
                }
                $coupon->name = $data['name'];
                $coupon->code = $data['code'];
                $coupon->type_coupon = $data['type_coupon'];
                $coupon->value = $data['value'];
                $coupon->products_id = $data['products_id'] ?? null;
                $coupon->type_value = $data['type_value'];
                $coupon->limit_time = $data['limit_time'] ?? null; //isset($data['key']) ? $data['key'] : null;
                $coupon->date_start = $data['date_start'] ?? null;
                $coupon->date_end = $data['date_end'] ?? null;
                $coupon->status = $data['status'] ?? 'active';
                $coupon->coupon_requests = $data['coupon_requests'] ?? null;
                $coupon->save();

                return response()->json(['status' => 'ok', 'message' => 'Cập nhật coupon thành công!']);

            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy dữ liệu!']);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function getValueByCode(): \Illuminate\Http\JsonResponse
    {
        $code = request()->input('coupon');
        $coupon = Coupon::where('code', $code)->first();
        $user = Auth::guard('api')->user();
        if (!$coupon) {
            return response()->json(['status' => 'error', 'message' => 'Mã giảm giá không tồn tại']);
        }
        $startDate = Carbon::parse($coupon->date_start);
        if ($startDate->isFuture()) {
            return response()->json(['status' => 'error', 'message' => 'Mã giảm giá không tồn tại']);
        }
        if ($coupon->status === "disable") {
            return response()->json(['status' => 'error', 'message' => 'Mã giảm giá đã bị vô hiệu hoá']);
        }
        if ($coupon->limit_time === 0) {
            return response()->json(['status' => 'error', 'message' => 'Mã giảm giá này đã hết lượt sử dụng']);
        }
        $endDate = Carbon::parse($coupon->date_end);
        if (isset($coupon->date_end) && $endDate->isPast()) {
            return response()->json(['status' => 'error', 'message' => 'Mã giảm giá đã hết hạn']);
        }
        if (!$user && (isset(json_decode($coupon->coupon_requests)->forRole) || isset(json_decode($coupon->coupon_requests)->forUser))) {
            return response()->json(['status' => 'error', 'message' => 'Bạn cần đăng nhập để sủ dụng mã này']);
        }
        if ($user && isset(json_decode($coupon->coupon_requests)->forUser) && $user->email !== json_decode($coupon->coupon_requests)->forUser) {
            return response()->json(['status' => 'error', 'message' => 'Mã giảm giá không dùng cho bạn']);
        }
        if ($user && isset(json_decode($coupon->coupon_requests)->forRole) && $user->getRoleNames()[0] !== json_decode($coupon->coupon_requests)->forRole) {
            return response()->json(['status' => 'error', 'message' => 'Mã giảm giá này chỉ dùng cho nhóm chỉ định']);
        }
        $formattedCoupon = [
            "id" => $coupon->id,
            "name" => $coupon->name,
            "code" => $coupon->code,
            "products_id" => $coupon->products_id,
            "type_coupon" => $coupon->type_coupon,
            "type_value" => $coupon->type_value,
            "value"=>$coupon->value,
            "status" => $coupon->status,
            "coupon_requests" => $coupon->coupon_requests
        ];
        return response()->json(['status' => 'ok', 'message' => 'Thêm mã giảm giá thành công', 'data' => $formattedCoupon]);
    }



//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
}
