<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ListAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ListAddressController extends Controller
{
    public function createAddress(Request $request): \Illuminate\Http\JsonResponse
    {

        $data = $request->all();
        try {
            $validator = Validator::make($data, [
                'name' => 'required',
                'address' => 'required',
                'ward' => 'required',
                'district' => 'required',
                'city' => 'required',
            ], [
                'required' => ':attribute là dữ liệu bắt buộc'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ]);
            }

            ListAddress::create([
                'user_id' => $request->user()->id,
                'name' => $data['name'],
                'address' => $data['address'],
                'ward' => $data['ward'],
                'district' => $data['district'],
                'city' => $data['city'],
            ]);

            return response()->json(['status' => 'ok', 'message' => 'Tạo địa chỉ thành công!']);

        } catch
        (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }

    public function showListAddressOfUser(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            $list = ListAddress::where('user_id', $user->id)->get();
            return response()->json($list);
        } catch
        (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }

    public function getOneAddressOfUserByID($id)
    {

        try {
            $address = Auth::user()->listAddresses()->find($id);
            if ($address) {
                return response()->json($address);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy dữ liệu!']);
            }
        } catch
        (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }
}
