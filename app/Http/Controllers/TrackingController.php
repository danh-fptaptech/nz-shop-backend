<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Tracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class TrackingController extends Controller
{
    public function createTracking(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->order_id &&  $request->deliver) {
            try {
                $order = Order::find($request->order_id);
                if($order->tracking_id){
                    return response()->json([
                        'status' => "error", 'message' => "Đã tồn tại quy trình vận chuyển"
                    ]);
                }
                $tracking = Tracking::create([
                    'order_id' => $request->order_id,
                    'deliver' => $request->deliver,
                    'status' => 'warehouse',
                ]);
//                if($request->deliver ==='ghtk'){
//                    $tracking->
//                }
                if($request->deliver !=='user'){
                    $tracking->deliver_info = 'ghtk';
                }
                $order->tracking_id = $tracking->id;
                $order->save();
                $tracking->user_id = $order->user_id;
                $tracking->save();
                return response()->json([
                    'status' => "ok", 'message' => "Tạo tiến trình vận chuyển thành công"
                ]);
            } catch
            (\Exception $e) {
                return response()->json([
                    'status' => "error",
                    'message' => substr($e->getMessage(), 0, 150),
                ]);
            }
        }else{
            return response()->json([
                'status' => "error", 'message' => "Không đủ dữ liệu"
            ]);
        }
    }
    public function updateDeliverInfo(Request $request,$id): \Illuminate\Http\JsonResponse
    {
        $tracking = Tracking::find($id);
        if ($tracking) {
            $tracking->deliver_info = $request->deliver_info;
            $tracking->save();
            return response()->json(['status' => 'ok', 'message' => 'Cập nhật thành công']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy quy trình vận chuyển']);
        }
    }
    public function fetchListTracking(): JsonResponse
    {
        $tracking = Tracking::with('order:id,order_code')->get();

        return response()->json($tracking);
    }
}
