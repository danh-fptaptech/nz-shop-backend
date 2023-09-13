<?php

namespace App\Http\Controllers;


use App\Mail\NewOrder;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function createOrder(Request $request): JsonResponse
    {

        $user = Auth::guard('api')->user();

//        Kiểm tra còn hàng không
        foreach ($request->input('items') as $item) {
            $productSku = $item['sku'];
            $skuArr = explode('-', $productSku);
            $product = Product::where('sku', 'like', $skuArr[0])->first();
            if (!$product) {
                return response()->json([
                    'status' => "error", 'message' => "Sản phẩm SKU ".$item['sku']." không tồn tại"
                ]);
            }
            if (count($skuArr) > 1) {
                $variant = json_decode($product->variants)[$skuArr[1] - 1];
                $product->quantity = $variant->quantity ?? $product->quantity;
            }
            if ($product->quantity < $item['quantity']) {
                return response()->json([
                    'status' => "error", 'message' => "Sản phẩm ".$product->name." không đủ hàng"
                ]);
            }
        }
        //      ->Tính tổng giá trị hàng hoá:
        $totalValue = array_reduce($request->input('items'), function ($total, $product) {
            $price = $product['info']['discount_price'] ?: $product['info']['sell_price'];
            return $total + (floatval($price) * $product['quantity']);
        }, 0);
//       Kiểm tra Coupon còn hiệu lực không
        if ($request->input(['coupon'])) {
            $CouponController = new CouponController();
            $coupon = $CouponController->getValueByCode();
            if ($coupon->original['status'] === "error") {
                return response()->json($coupon->original);
            }
            $dataCoupon = ((object) $coupon->original['data']);
            if (json_decode($dataCoupon->coupon_requests) && json_decode($dataCoupon->coupon_requests)->MinCart && json_decode($dataCoupon->coupon_requests)->MinCart > $totalValue) {
                return response()->json([
                    'status' => "error", 'message' => "Không đủ điều kiện dùng mã giảm giá"
                ]);
            }
            $valueCoupon = $dataCoupon->value;
            $typeCoupon = $dataCoupon->type_coupon;
            $typeVCoupon = $dataCoupon->type_value;
        }

        $order = Order::create([
            'email_buyer' => $request->input('emailBuyer'),
            'phone_number_tracking' => $request->input('phoneNumberTracking'),
            'address_shipping' => json_encode($request->input('addressShipping')),
            'items' => json_encode($request->input('items')),
            'delivery' => json_encode($request->input('delivery')),
        ]);
//        Xử lý Coupon
        if ($request->input('coupon')) {
            $selectedCoupon = Coupon::where('code', $request->input('coupon'))->first();
            if ($selectedCoupon && $selectedCoupon->limit_time > 0) {
                $selectedCoupon->limit_time--;
                $selectedCoupon->save();
            }
        }

//        Xử lý trừ stock khi đã mua hàng
        foreach ($request->input('items') as $item) {
            $productSku = $item['sku'];
            $skuArr = explode('-', $productSku);
            $product = Product::where('sku', 'like', $skuArr[0])->first();
            if (count($skuArr) > 1) {
                $getVariant = json_decode($product->variants);
                $getVariant[$skuArr[1] - 1]->quantity -= $item['quantity'];
                $product->variants = json_encode($getVariant);
            }
            $product->quantity -= $item['quantity'];
            $product->save();
        }
//        End xử lý stock

            $valueDiscount = 0;
            $priceShipping = json_decode($order->delivery)->value;


//        ->Tính phí ship khách hàng trả:
//        ->Nếu có mã coupon miễn phí vận chuyển:
        if ($request->input(['coupon']) && $typeVCoupon === 'free_shipping') {
            $valueDiscount = $priceShipping;
            $priceShipping = 0;
        }
//        ->Nếu có mã coupon giảm giá vận chuyển:
        if ($request->input(['coupon']) && $typeVCoupon === 'reduce_shipping') {
            $priceShipping = max($priceShipping - $valueCoupon, 0);
            $valueDiscount = $priceShipping > 0 ? $valueCoupon : json_decode($order->delivery)->value;
        }

        if ($request->input(['coupon']) && $typeVCoupon === 'number_value' && $typeCoupon === 'totalcart') {
            $valueDiscount = $valueCoupon;
        }
        if ($request->input(['coupon']) && $typeVCoupon === 'percent_value' && $typeCoupon === 'totalcart') {
            $valueDiscount = floatval(($totalValue / 100) * $valueCoupon);
        }
//      Tính giá gốc

        $costOfGoods = array_reduce($request->input('items'), function ($total, $product) {
            $price = $product['info']['origin_price'];
            return $total + (floatval($price) * $product['quantity']);
        }, 0);

        $calProfit = $totalValue - $costOfGoods - $valueDiscount;

        $calTotalOrder = $totalValue - $valueDiscount + json_decode($order->delivery)->value;
        $order->order_code = 'NZ'.Carbon::now()->format('dmy').'I'.$order->id;
        $order->total_value = $totalValue;
        $order->price_shipping = $priceShipping;
        $order->value_discount = $valueDiscount;
        $order->total_order = $calTotalOrder;
        $order->cost_of_goods = $costOfGoods;
        $order->profit = $calProfit;
        if ($user) {
            $order->user_id = $user->id;
        }
        if ($request->input(['coupon'])) {
            $order->coupon = $request->input(['coupon']);
        }
        $order->save();
        $linkcheckorder = config('app.url')."/check-order/".$order->order_code."?key=".md5($order->email_buyer);
        Mail::to($order->email_buyer)->send(new NewOrder($linkcheckorder));
        return response()->json([
            'status' => "ok", 'message' => "Tạo đơn hàng thành công", 'data' => $order->order_code
        ]);
    }

    public function fetchListOrder(): JsonResponse
    {
        $orders = Order::with('users', 'trackings', 'transactions')->get();

        return response()->json($orders);
    }
    public function fetchOrdersOfUser(): JsonResponse
    {
        $user = request()->user();
        $orders = Order::where('user_id', $user->id)
            ->with('users', 'trackings', 'transactions')->orderByDesc('created_at');
        $orderList = $orders->paginate(10);
//        Tổng chi tiêu
        $totalPay = array_reduce($orders->whereNotNull('transaction_id')->get()->toArray(), function ($total, $order) {
            return $total + $order['total_order'];
        }, 0);
        return response()->json(['list'=>$orderList,'Total'=>$totalPay]);
    }

    public function orderByCode($code): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $keyhash = request()->query('key');
        if($user){
            $keyhash = md5($user->email);
        }
        $order = Order::where('order_code', $code)->with('users', 'trackings', 'transactions')->first();
        if (!$order) {
            return response()->json(['status' => "error", 'message' => "Không tìm thấy đơn hàng có mã '$code'"]);
        }
        if ($user && $order->user_id && $order->user_id !== $user->id) {
            return response()->json(['status' => "error", 'message' => "Bạn không có quyền truy cập đơn hàng này"]);
        }
        if($keyhash != md5($order->email_buyer)){
            return response()->json(['status' => "error",'key1'=>$keyhash,'key2'=>md5($order->email_buyer), 'message' => "Bạn không có quyền truy cập đơn hàng này"]);
        }

        return response()->json(['data' => $order]);
    }
}
