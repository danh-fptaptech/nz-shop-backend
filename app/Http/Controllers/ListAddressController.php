<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ListAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ListAddressController extends Controller
{
    public function createAddress(Request $request):JsonResponse
    {

        $data = $request->all();
        try {
            $validator = Validator::make($data, [
                'name' => 'required',
                'phone_number' => 'bail|required|string|regex:/^[0-9]{10}$/',
                'address' => 'required',
                'ward' => 'required',
                'district' => 'required',
                'city' => 'required',
            ], [
                'required' => ':attribute là dữ liệu bắt buộc',
                'phone_number.regex' => 'Số điện thoại không đúng định dạng',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ]);
            }
            $addressCount = $request->user()->listAddresses()->count();
            if($addressCount>=5){
                return response()->json([
                    'status' => 'error', 'message' => "Mỗi người chỉ được tối đa 5 địa chỉ"
                ]);
            }
            $existingAddress = ListAddress::where('user_id', $request->user()->id)
                ->where('name', $data['name'])
                ->first();
            if ($existingAddress) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tên Địa chỉ đã tồn tại trong danh sách của bạn.'
                ]);
            }
            ListAddress::create([
                'user_id' => $request->user()->id,
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
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

    public function getOneAddressOfUserByID($id): \Illuminate\Http\JsonResponse
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

    public function editAddressByID(Request $request,$id): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        try {
            $address = Auth::user()->listAddresses()->find($id);
            if ($address) {
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
                $address->name = $data['name'];
                $address->address = $data['address'];
                $address->ward = $data['ward'];
                $address->district = $data['district'];
                $address->city = $data['city'];
                $address->save();
                return response()->json(['status' => 'ok', 'message' => 'Cập nhật địa chỉ thành công!']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy dữ liệu!']);
            }
        } catch
        (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }

    public function deleteAddress($id): \Illuminate\Http\JsonResponse
    {
        try {
            $address = Auth::user()->listAddresses()->find($id);
            $address->delete();
            return response()->json(['status' => 'ok', 'message' => 'Xoá địa chỉ thành công']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }
    }
}
