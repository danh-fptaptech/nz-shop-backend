<?php

namespace App\Http\Controllers;


use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function createOrder(Request $request): JsonResponse
    {



        $user = Auth::guard('api')->user();


        //        Kiểm tra còn hàng không
        $errors = "";
        foreach ($request->input('items') as $item) {
            $productSku = $item['sku'];
            $skuArr = explode('-', $productSku);
            $product = Product::where('sku', 'like', $skuArr[0])->first();
            if (count($skuArr) > 1) {
                $variant = json_decode($product->variants)[$skuArr[1] - 1];
                $product->quantity = $variant->quantity?:0;
            }
            if (!$product && $product->quantity < 1) {
                $errors .= "Sản phẩm ".$product->name." đã hết hàng \n";
            }
        }
        if (strlen($errors) > 3) {
            return response()->json([
                'status' => "error", 'message' => $errors
            ]);
        }
        //       Kiểm tra Coupon còn hiệu lực không

        $dataCoupon ="";
        if ($request->input(['coupon'])) {
            $CouponController = new CouponController();
            $coupon = $CouponController->getValueByCode();
            if($coupon->original['status']==="error"){
                return response()->json($coupon->original);
            }
            $dataCoupon =$coupon->original['data'];
        }




        $order = Order::create([
            'email_buyer' => $request->input('emailBuyer'),
            'phone_number_tracking' => $request->input('phoneNumberTracking'),
            'address_shipping' => json_encode($request->input('addressShipping')),
            'items' => json_encode($request->input('items')),
            'delivery' => json_encode($request->input('delivery')),
        ]);

        $priceShipping = json_decode($order->delivery)->value;
        $valueCoupon = ((object) $dataCoupon)->value;
        $typeCoupon = ((object) $dataCoupon)->type_coupon;
        $typeVCoupon = ((object) $dataCoupon)->type_value;

        if ($request->input(['coupon']) && $typeVCoupon ==='free_shipping') {
            $priceShipping = 0;
        }
        if ($request->input(['coupon']) && $typeVCoupon ==='reduce_shipping') {
            $calculatePrice = $priceShipping - $valueCoupon;
            $priceShipping = max($calculatePrice, 0);
        }
//        $priceShipping thêm vào db chưa có => lát thêm cột



//      Tính giá gốc

        $costOfGoods = array_reduce($request->input('items'), function ($total, $product) {
            $price = $product['info']['origin_price'];
            return $total + (floatval($price) * $product['quantity']);
        }, 0);

        $order->cost_of_goods = $costOfGoods;

//      Tính tổng cart

        $totalValue = array_reduce($request->input('items'), function ($total, $product) {
            $price = $product['info']['discount_price'] ?: $product['info']['sell_price'];
            return $total + (floatval($price) * $product['quantity']);
        }, 0);



        $order->total_value = $totalValue;

        $order->profit = $totalValue - $costOfGoods;

        if ($user) {
            $order->user_id = $user->id;
        }
        if ($request->input(['coupon'])) {
            $order->coupon = $request->input(['coupon']);
        }
        $order->save();
        return response()->json([
            'status' => "ok", 'message' => "Nhận dữ liệu thành công", 'data' => $order->id
        ]);
    }
}
