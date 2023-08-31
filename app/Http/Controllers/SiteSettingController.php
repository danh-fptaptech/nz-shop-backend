<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiteSettingController extends Controller
{
    public function createOne(Request $request): JsonResponse
    {
        $data = $request->all();
        try {
            $validator = Validator::make($data, [
                'key_setting' => 'bail|required|unique:site_settings|regex:/^[a-zA-Z_]{2,30}$/',
                'value_setting' => 'bail|required|regex:/^[0-9a-zA-Z\-]{5,50}$/',
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
}
